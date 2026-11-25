import type { ApiErrorPayload } from '../../types/api';

export class ApiError extends Error {
  public readonly status: number;
  public readonly code: string;
  public readonly errors?: Record<string, string[]>;

  public constructor(status: number, payload: ApiErrorPayload) {
    super(payload.message);
    this.name = 'ApiError';
    this.status = status;
    this.code = payload.code;
    if (payload.errors !== undefined) {
      this.errors = payload.errors;
    }
  }
}

export interface ApiFetchOptions extends Omit<RequestInit, 'headers'> {
  token?: string;
  headers?: Record<string, string>;
  baseUrl?: string;
}

export type ApiRequestContext =
  | string
  | {
      token?: string;
      baseUrl?: string;
    };

export function normalizeApiContext(context?: ApiRequestContext): {
  token?: string;
  baseUrl?: string;
} {
  if (context === undefined) {
    return {};
  }
  if (typeof context === 'string') {
    return { baseUrl: context };
  }
  return context;
}

export function resolveApiBaseUrl(): string {
  if (typeof window !== 'undefined') {
    return '/api/proxy';
  }

  const internalUrl =
    typeof process !== 'undefined'
      ? process.env['API_INTERNAL_URL']
      : undefined;
  const publicUrl = import.meta.env.PUBLIC_API_URL as string | undefined;

  if (internalUrl && internalUrl.length > 0) {
    return internalUrl.replace(/\/+$/, '');
  }

  if (publicUrl && publicUrl.length > 0) {
    return publicUrl.replace(/\/+$/, '');
  }

  return 'http://localhost:8000/api/v1';
}

export async function apiFetch<T>(
  path: string,
  options: ApiFetchOptions = {},
): Promise<T> {
  const baseUrl = (options.baseUrl ?? resolveApiBaseUrl()).replace(/\/+$/, '');
  const normalizedPath = path.startsWith('/') ? path : `/${path}`;
  const headers: Record<string, string> = {
    Accept: 'application/json',
    ...options.headers,
  };

  if (options.body !== undefined && !(options.body instanceof FormData)) {
    headers['Content-Type'] = headers['Content-Type'] ?? 'application/json';
  }

  if (options.token) {
    headers['Authorization'] = `Bearer ${options.token}`;
  }

  const requestInit: RequestInit = {
    ...options,
    headers,
  };

  const response = await fetch(`${baseUrl}${normalizedPath}`, requestInit);

  if (response.status === 204) {
    return undefined as T;
  }

  let parsed: unknown = null;
  const contentType = response.headers.get('content-type') ?? '';
  if (contentType.includes('application/json')) {
    parsed = await response.json();
  }

  if (!response.ok) {
    const payload: ApiErrorPayload = isApiErrorPayload(parsed)
      ? parsed
      : {
          message: `Error HTTP ${response.status}`,
          code: 'http_error',
        };
    throw new ApiError(response.status, payload);
  }

  return parsed as T;
}

function isApiErrorPayload(value: unknown): value is ApiErrorPayload {
  if (typeof value !== 'object' || value === null) {
    return false;
  }

  const record = value as Record<string, unknown>;
  return (
    typeof record['message'] === 'string' && typeof record['code'] === 'string'
  );
}
