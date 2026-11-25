import type { ApiResource, PaginatedResponse } from '../../types/api';
import type {
  Product,
  ProductInput,
  ProductListQuery,
} from '../../types/product';
import { apiFetch } from './client';

export async function listProducts(
  query: ProductListQuery = {},
  baseUrl?: string,
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
    baseUrl !== undefined ? { baseUrl } : {},
  );
}

export async function createProduct(
  input: ProductInput,
  baseUrl?: string,
): Promise<Product> {
  const response = await apiFetch<ApiResource<Product>>('/products', {
    method: 'POST',
    body: JSON.stringify(input),
    ...(baseUrl !== undefined ? { baseUrl } : {}),
  });
  return response.data;
}

export async function updateProduct(
  id: number,
  input: ProductInput,
  baseUrl?: string,
): Promise<Product> {
  const response = await apiFetch<ApiResource<Product>>(`/products/${id}`, {
    method: 'PUT',
    body: JSON.stringify(input),
    ...(baseUrl !== undefined ? { baseUrl } : {}),
  });
  return response.data;
}

export async function uploadProductImage(
  id: number,
  file: File,
  baseUrl?: string,
): Promise<Product> {
  const formData = new FormData();
  formData.append('image', file);

  const response = await apiFetch<ApiResource<Product>>(
    `/products/${id}/image`,
    {
      method: 'POST',
      body: formData,
      ...(baseUrl !== undefined ? { baseUrl } : {}),
    },
  );
  return response.data;
}

export async function deleteProduct(
  id: number,
  baseUrl?: string,
): Promise<void> {
  await apiFetch<undefined>(`/products/${id}`, {
    method: 'DELETE',
    ...(baseUrl !== undefined ? { baseUrl } : {}),
  });
}
