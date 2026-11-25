export interface Waiter {
  id: number;
  name: string;
  email: string;
  is_active: boolean;
  created_at: string | null;
  updated_at: string | null;
}

export interface WaiterInput {
  name: string;
  email: string;
  is_active: boolean;
}

export interface WaiterListQuery {
  name?: string;
  isActive?: boolean;
  sort?: 'id' | '-id' | 'name' | '-name' | 'created_at' | '-created_at';
  page?: number;
  perPage?: number;
}
