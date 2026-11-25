import type { Category } from './category';

export interface Product {
  id: number;
  name: string;
  description: string | null;
  category_id: number;
  category?: Category;
  price_cents: number;
  image_url: string | null;
  created_at: string | null;
  updated_at: string | null;
}

export interface ProductInput {
  name: string;
  category_id: number;
  price_cents: number;
  description: string | null;
}

export interface ProductListQuery {
  name?: string;
  categoryId?: number;
  sort?:
    | 'id'
    | '-id'
    | 'name'
    | '-name'
    | 'price_cents'
    | '-price_cents'
    | 'created_at'
    | '-created_at';
  page?: number;
  perPage?: number;
}
