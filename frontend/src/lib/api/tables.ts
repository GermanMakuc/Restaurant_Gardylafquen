import type { ApiResource, PaginatedResponse } from '../../types/api';
import type {
  RestaurantTable,
  TableInput,
  TableListQuery,
} from '../../types/table';
import {
  apiFetch,
  normalizeApiContext,
  type ApiRequestContext,
} from './client';

export async function listTables(
  query: TableListQuery = {},
  context?: ApiRequestContext,
): Promise<PaginatedResponse<RestaurantTable>> {
  const params = new URLSearchParams();

  if (query.status !== undefined) {
    params.set('filter[status]', query.status);
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
  const path = qs.length > 0 ? `/tables?${qs}` : '/tables';

  return apiFetch<PaginatedResponse<RestaurantTable>>(
    path,
    normalizeApiContext(context),
  );
}

export async function createTable(
  input: TableInput,
  context?: ApiRequestContext,
): Promise<RestaurantTable> {
  const response = await apiFetch<ApiResource<RestaurantTable>>('/tables', {
    method: 'POST',
    body: JSON.stringify(input),
    ...normalizeApiContext(context),
  });
  return response.data;
}

export async function updateTable(
  id: number,
  input: TableInput,
  context?: ApiRequestContext,
): Promise<RestaurantTable> {
  const response = await apiFetch<ApiResource<RestaurantTable>>(
    `/tables/${id}`,
    {
      method: 'PUT',
      body: JSON.stringify(input),
      ...normalizeApiContext(context),
    },
  );
  return response.data;
}

export async function deleteTable(
  id: number,
  context?: ApiRequestContext,
): Promise<void> {
  await apiFetch<undefined>(`/tables/${id}`, {
    method: 'DELETE',
    ...normalizeApiContext(context),
  });
}
