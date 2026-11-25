import type { ApiResource, PaginatedResponse } from '../../types/api';
import type { Waiter, WaiterInput, WaiterListQuery } from '../../types/waiter';
import { apiFetch } from './client';

export async function listWaiters(
  query: WaiterListQuery = {},
  baseUrl?: string,
): Promise<PaginatedResponse<Waiter>> {
  const params = new URLSearchParams();

  if (query.name !== undefined && query.name.trim() !== '') {
    params.set('filter[name]', query.name.trim());
  }
  if (query.isActive !== undefined) {
    params.set('filter[is_active]', query.isActive ? 'true' : 'false');
  }
  if (query.sort !== undefined) {
    params.set('sort', query.sort);
  }
  if (query.page !== undefined) {
    params.set('page', String(query.page));
  }
  if (query.perPage !== undefined) {
    params.set('per_page', String(query.perPage));
  }

  const qs = params.toString();
  const path = qs.length > 0 ? `/waiters?${qs}` : '/waiters';

  return apiFetch<PaginatedResponse<Waiter>>(
    path,
    baseUrl !== undefined ? { baseUrl } : {},
  );
}

export async function createWaiter(
  input: WaiterInput,
  baseUrl?: string,
): Promise<Waiter> {
  const response = await apiFetch<ApiResource<Waiter>>('/waiters', {
    method: 'POST',
    body: JSON.stringify(input),
    ...(baseUrl !== undefined ? { baseUrl } : {}),
  });
  return response.data;
}

export async function updateWaiter(
  id: number,
  input: WaiterInput,
  baseUrl?: string,
): Promise<Waiter> {
  const response = await apiFetch<ApiResource<Waiter>>(`/waiters/${id}`, {
    method: 'PUT',
    body: JSON.stringify(input),
    ...(baseUrl !== undefined ? { baseUrl } : {}),
  });
  return response.data;
}

export async function deleteWaiter(
  id: number,
  baseUrl?: string,
): Promise<void> {
  await apiFetch<undefined>(`/waiters/${id}`, {
    method: 'DELETE',
    ...(baseUrl !== undefined ? { baseUrl } : {}),
  });
}
