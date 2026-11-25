import type { ApiResource, HealthStatus } from '../../types/api';
import { apiFetch } from './client';

export async function getHealthStatus(baseUrl?: string): Promise<HealthStatus> {
  const response = await apiFetch<ApiResource<HealthStatus>>(
    '/health',
    baseUrl !== undefined ? { baseUrl } : {},
  );
  return response.data;
}
