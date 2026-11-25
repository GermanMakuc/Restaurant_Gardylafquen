import type { ApiResource, PaginatedResponse } from '../../types/api';
import type {
  RestaurantTable,
  TableInput,
  TableListQuery,
} from '../../types/table';
import { apiFetch } from './client';

export async function listTables(
  query: TableListQuery = {},
  baseUrl?: string,
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
    baseUrl !== undefined ? { baseUrl } : {},
  );
}

export async function createTable(
  input: TableInput,
  baseUrl?: string,
): Promise<RestaurantTable> {
  const response = await apiFetch<ApiResource<RestaurantTable>>('/tables', {
    method: 'POST',
    body: JSON.stringify(input),
    ...(baseUrl !== undefined ? { baseUrl } : {}),
  });
  return response.data;
}

export async function updateTable(
  id: number,
  input: TableInput,
  baseUrl?: string,
): Promise<RestaurantTable> {
  const response = await apiFetch<ApiResource<RestaurantTable>>(
    `/tables/${id}`,
    {
      method: 'PUT',
      body: JSON.stringify(input),
      ...(baseUrl !== undefined ? { baseUrl } : {}),
    },
  );
  return response.data;
}

export async function deleteTable(id: number, baseUrl?: string): Promise<void> {
  await apiFetch<undefined>(`/tables/${id}`, {
    method: 'DELETE',
    ...(baseUrl !== undefined ? { baseUrl } : {}),
  });
}
