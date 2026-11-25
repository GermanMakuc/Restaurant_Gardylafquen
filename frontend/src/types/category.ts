export interface Category {
  id: number;
  name: string;
  created_at: string | null;
  updated_at: string | null;
}

export interface CategoryInput {
  name: string;
}

export interface CategoryListQuery {
  name?: string;
  sort?: 'id' | '-id' | 'name' | '-name' | 'created_at' | '-created_at';
  page?: number;
  perPage?: number;
}
