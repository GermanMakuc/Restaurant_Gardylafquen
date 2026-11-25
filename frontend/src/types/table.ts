export type TableStatus = 'available' | 'occupied' | 'reserved' | 'inactive';

export interface RestaurantTable {
  id: number;
  number: number;
  label: string | null;
  capacity: number;
  status: TableStatus;
  created_at: string | null;
  updated_at: string | null;
}

export interface TableInput {
  number: number;
  capacity: number;
  status: TableStatus;
  label: string | null;
}

export interface TableListQuery {
  status?: TableStatus;
  sort?:
    | 'id'
    | '-id'
    | 'number'
    | '-number'
    | 'capacity'
    | '-capacity'
    | 'created_at'
    | '-created_at';
  page?: number;
  perPage?: number;
}
