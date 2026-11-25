import type { ApiResource, PaginatedResponse } from '../../types/api';
import type {
  Category,
  CategoryInput,
  CategoryListQuery,
} from '../../types/category';
import { apiFetch } from './client';

export async function listCategories(
  query: CategoryListQuery = {},
  baseUrl?: string,
): Promise<PaginatedResponse<Category>> {
  const params = new URLSearchParams();

  if (query.name !== undefined && query.name.trim() !== '') {
    params.set('filter[name]', query.name.trim());
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
  const path = qs.length > 0 ? `/categories?${qs}` : '/categories';

  return apiFetch<PaginatedResponse<Category>>(
    path,
    baseUrl !== undefined ? { baseUrl } : {},
  );
}

export async function createCategory(
  input: CategoryInput,
  baseUrl?: string,
): Promise<Category> {
  const response = await apiFetch<ApiResource<Category>>('/categories', {
    method: 'POST',
    body: JSON.stringify(input),
    ...(baseUrl !== undefined ? { baseUrl } : {}),
  });
  return response.data;
}

export async function updateCategory(
  id: number,
  input: CategoryInput,
  baseUrl?: string,
): Promise<Category> {
  const response = await apiFetch<ApiResource<Category>>(`/categories/${id}`, {
    method: 'PUT',
    body: JSON.stringify(input),
    ...(baseUrl !== undefined ? { baseUrl } : {}),
  });
  return response.data;
}

export async function deleteCategory(
  id: number,
  baseUrl?: string,
): Promise<void> {
  await apiFetch<undefined>(`/categories/${id}`, {
    method: 'DELETE',
    ...(baseUrl !== undefined ? { baseUrl } : {}),
  });
}
