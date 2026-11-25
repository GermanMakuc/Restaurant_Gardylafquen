import type { ApiErrorPayload, ApiResource } from '../../types/api';
import type { AuthTokenData, AuthUser, LoginInput } from '../../types/auth';
import {
  ApiError,
  apiFetch,
  normalizeApiContext,
  type ApiRequestContext,
} from './client';

export async function loginWithBackend(
  input: LoginInput,
  baseUrl?: string,
): Promise<AuthTokenData> {
  const response = await apiFetch<ApiResource<AuthTokenData>>('/auth/login', {
    method: 'POST',
    body: JSON.stringify(input),
    ...(baseUrl !== undefined ? { baseUrl } : {}),
  });
  return response.data;
}

export async function getCurrentUser(
  context?: ApiRequestContext,
): Promise<AuthUser> {
  const response = await apiFetch<ApiResource<AuthUser>>(
    '/auth/me',
    normalizeApiContext(context),
  );
  return response.data;
}

export async function logoutFromBackend(
  token: string,
  baseUrl?: string,
): Promise<void> {
  await apiFetch<undefined>('/auth/logout', {
    method: 'POST',
    token,
    ...(baseUrl !== undefined ? { baseUrl } : {}),
  });
}

export async function submitLogin(
  input: LoginInput,
  endpoint = '/api/auth/login',
): Promise<{ user: AuthUser }> {
  const response = await fetch(endpoint, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(input),
  });

  let parsed: unknown = null;
  const contentType = response.headers.get('content-type') ?? '';
  if (contentType.includes('application/json')) {
    parsed = await response.json();
  }

  if (!response.ok) {
    const payload = isErrorPayload(parsed)
      ? parsed
      : {
          message: `Error HTTP ${response.status}`,
          code: 'http_error',
        };
    throw new ApiError(response.status, payload);
  }

  const envelope = parsed as ApiResource<{ user: AuthUser }>;
  return envelope.data;
}

function isErrorPayload(value: unknown): value is ApiErrorPayload {
  if (typeof value !== 'object' || value === null) {
    return false;
  }
  const record = value as Record<string, unknown>;
  return (
    typeof record['message'] === 'string' && typeof record['code'] === 'string'
  );
}
