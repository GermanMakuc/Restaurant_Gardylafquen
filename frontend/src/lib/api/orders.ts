import type { ApiResource, PaginatedResponse } from '../../types/api';
import type {
  Order,
  OrderInput,
  OrderListQuery,
  OrderStatus,
} from '../../types/order';
import {
  apiFetch,
  normalizeApiContext,
  type ApiRequestContext,
} from './client';

export async function listOrders(
  query: OrderListQuery = {},
  context?: ApiRequestContext,
): Promise<PaginatedResponse<Order>> {
  const params = new URLSearchParams();

  if (query.status !== undefined) {
    params.set('filter[status]', query.status);
  }
  if (query.tableId !== undefined && query.tableId > 0) {
    params.set('filter[table_id]', String(query.tableId));
  }
  if (query.waiterId !== undefined && query.waiterId > 0) {
    params.set('filter[waiter_id]', String(query.waiterId));
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
  const path = qs.length > 0 ? `/orders?${qs}` : '/orders';

  return apiFetch<PaginatedResponse<Order>>(path, normalizeApiContext(context));
}

export async function createOrder(
  input: OrderInput,
  context?: ApiRequestContext,
): Promise<Order> {
  const response = await apiFetch<ApiResource<Order>>('/orders', {
    method: 'POST',
    body: JSON.stringify(input),
    ...normalizeApiContext(context),
  });
  return response.data;
}

export async function updateOrder(
  id: number,
  input: OrderInput,
  context?: ApiRequestContext,
): Promise<Order> {
  const response = await apiFetch<ApiResource<Order>>(`/orders/${id}`, {
    method: 'PUT',
    body: JSON.stringify(input),
    ...normalizeApiContext(context),
  });
  return response.data;
}

export async function updateOrderStatus(
  id: number,
  status: Extract<OrderStatus, 'waiting' | 'served' | 'cancelled'>,
  context?: ApiRequestContext,
): Promise<Order> {
  const response = await apiFetch<ApiResource<Order>>(`/orders/${id}/status`, {
    method: 'PATCH',
    body: JSON.stringify({ status }),
    ...normalizeApiContext(context),
  });
  return response.data;
}

export async function deleteOrder(
  id: number,
  context?: ApiRequestContext,
): Promise<void> {
  await apiFetch<undefined>(`/orders/${id}`, {
    method: 'DELETE',
    ...normalizeApiContext(context),
  });
}
