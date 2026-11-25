import type { Order } from './order';

export interface Sale {
  id: number;
  order_id: number;
  order?: Order;
  tip_percent: number;
  subtotal_cents: number;
  tip_cents: number;
  total_cents: number;
  paid_at: string;
  created_at: string | null;
}

export interface SaleInput {
  order_id: number;
  tip_percent: number;
}

export interface SalesSummary {
  count: number;
  subtotal_cents: number;
  tip_cents: number;
  total_cents: number;
}

export interface SaleListQuery {
  from?: string;
  to?: string;
  sort?: 'id' | '-id' | 'paid_at' | '-paid_at' | 'total_cents' | '-total_cents';
  page?: number;
  perPage?: number;
}
