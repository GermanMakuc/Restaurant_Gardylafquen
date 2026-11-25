import type { RestaurantTable } from './table';
import type { Waiter } from './waiter';

export type OrderStatus = 'waiting' | 'served' | 'paid' | 'cancelled';

export interface OrderItem {
  id: number;
  product_id: number;
  product_name: string;
  unit_price_cents: number;
  quantity: number;
  line_total_cents: number;
}

export interface Order {
  id: number;
  table_id: number;
  table?: RestaurantTable;
  waiter_id: number;
  waiter?: Waiter;
  status: OrderStatus;
  notes: string | null;
  subtotal_cents: number;
  items?: OrderItem[];
  created_at: string | null;
  updated_at: string | null;
}

export interface OrderItemInput {
  product_id: number;
  quantity: number;
}

export interface OrderInput {
  table_id: number;
  waiter_id: number;
  notes: string | null;
  items: OrderItemInput[];
}

export interface OrderListQuery {
  status?: OrderStatus;
  tableId?: number;
  waiterId?: number;
  sort?:
    | 'id'
    | '-id'
    | 'subtotal_cents'
    | '-subtotal_cents'
    | 'created_at'
    | '-created_at';
  page?: number;
  perPage?: number;
}
