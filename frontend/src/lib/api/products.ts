import type { ApiResource, PaginatedResponse } from '../../types/api';
import type {
  Product,
  ProductInput,
  ProductListQuery,
} from '../../types/product';
import {
  apiFetch,
  normalizeApiContext,
  type ApiRequestContext,
} from './client';

export async function listProducts(
  query: ProductListQuery = {},
  context?: ApiRequestContext,
): Promise<PaginatedResponse<Product>> {
  const params = new URLSearchParams();

  if (query.name !== undefined && query.name.trim() !== '') {
    params.set('filter[name]', query.name.trim());
  }
  if (query.categoryId !== undefined && query.categoryId > 0) {
    params.set('filter[category_id]', String(query.categoryId));
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
  const path = qs.length > 0 ? `/products?${qs}` : '/products';

  return apiFetch<PaginatedResponse<Product>>(
    path,
    normalizeApiContext(context),
  );
}

export async function createProduct(
  input: ProductInput,
  context?: ApiRequestContext,
): Promise<Product> {
  const response = await apiFetch<ApiResource<Product>>('/products', {
    method: 'POST',
    body: JSON.stringify(input),
    ...normalizeApiContext(context),
  });
  return response.data;
}

export async function updateProduct(
  id: number,
  input: ProductInput,
  context?: ApiRequestContext,
): Promise<Product> {
  const response = await apiFetch<ApiResource<Product>>(`/products/${id}`, {
    method: 'PUT',
    body: JSON.stringify(input),
    ...normalizeApiContext(context),
  });
  return response.data;
}

export async function uploadProductImage(
  id: number,
  file: File,
  context?: ApiRequestContext,
): Promise<Product> {
  const formData = new FormData();
  formData.append('image', file);

  const response = await apiFetch<ApiResource<Product>>(
    `/products/${id}/image`,
    {
      method: 'POST',
      body: formData,
      ...normalizeApiContext(context),
    },
  );
  return response.data;
}

export async function deleteProduct(
  id: number,
  context?: ApiRequestContext,
): Promise<void> {
  await apiFetch<undefined>(`/products/${id}`, {
    method: 'DELETE',
    ...normalizeApiContext(context),
  });
}
