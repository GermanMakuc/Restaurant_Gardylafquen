import type { ApiResource, PaginatedResponse } from '../../types/api';
import type {
  Sale,
  SaleInput,
  SaleListQuery,
  SalesSummary,
} from '../../types/sale';
import { apiFetch } from './client';

function buildSalesQuery(
  query: SaleListQuery,
  includeSortAndPaging: boolean,
): string {
  const params = new URLSearchParams();

  if (query.from !== undefined && query.from.trim() !== '') {
    params.set('filter[from]', query.from.trim());
  }
  if (query.to !== undefined && query.to.trim() !== '') {
    params.set('filter[to]', query.to.trim());
  }

  if (includeSortAndPaging) {
    if (query.sort !== undefined) {
      params.set('sort', query.sort);
    }
    if (query.page !== undefined) {
      params.set('page', String(query.page));
    }
    if (query.perPage !== undefined) {
      params.set('per_page', String(query.perPage));
    }
  }

  return params.toString();
}

export async function listSales(
  query: SaleListQuery = {},
  baseUrl?: string,
): Promise<PaginatedResponse<Sale>> {
  const qs = buildSalesQuery(query, true);
  const path = qs.length > 0 ? `/sales?${qs}` : '/sales';

  return apiFetch<PaginatedResponse<Sale>>(
    path,
    baseUrl !== undefined ? { baseUrl } : {},
  );
}

export async function getSalesSummary(
  query: Pick<SaleListQuery, 'from' | 'to'> = {},
  baseUrl?: string,
): Promise<SalesSummary> {
  const qs = buildSalesQuery(query, false);
  const path = qs.length > 0 ? `/sales/summary?${qs}` : '/sales/summary';

  const response = await apiFetch<ApiResource<SalesSummary>>(
    path,
    baseUrl !== undefined ? { baseUrl } : {},
  );
  return response.data;
}

export async function createSale(
  input: SaleInput,
  baseUrl?: string,
): Promise<Sale> {
  const response = await apiFetch<ApiResource<Sale>>('/sales', {
    method: 'POST',
    body: JSON.stringify(input),
    ...(baseUrl !== undefined ? { baseUrl } : {}),
  });
  return response.data;
}
