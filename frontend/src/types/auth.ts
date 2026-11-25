export type UserRole = 'admin' | 'cashier' | 'waiter';

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  role: UserRole;
  created_at: string | null;
}

export interface LoginInput {
  email: string;
  password: string;
}

export interface AuthTokenData {
  token: string;
  token_type: 'Bearer';
  user: AuthUser;
}
