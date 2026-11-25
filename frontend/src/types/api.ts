export interface ApiResource<T> {
  data: T;
}

export interface PaginationLinks {
  first: string | null;
  last: string | null;
  prev: string | null;
  next: string | null;
}

export interface PaginationMeta {
  current_page: number;
  from: number | null;
  last_page: number;
  path: string;
  per_page: number;
  to: number | null;
  total: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  links: PaginationLinks;
  meta: PaginationMeta;
}

export interface ApiErrorPayload {
  message: string;
  code: string;
  errors?: Record<string, string[]>;
}

export interface HealthStatus {
  status: 'ok' | 'degraded';
  service: string;
  database: 'ok' | 'error';
  timestamp: string;
}
