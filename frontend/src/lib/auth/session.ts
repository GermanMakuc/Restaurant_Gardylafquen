import type { AstroCookies } from 'astro';
import type { AuthUser } from '../../types/auth';
import { getCurrentUser } from '../api/auth';
import { ApiError } from '../api/client';

export const AUTH_COOKIE_NAME = 'gardylafquen_token';

const COOKIE_MAX_AGE_SECONDS = 60 * 60 * 24 * 7;

export interface ServerSession {
  token: string;
  user: AuthUser;
}

export function getAuthToken(cookies: AstroCookies): string | undefined {
  const raw = cookies.get(AUTH_COOKIE_NAME)?.value;
  if (raw === undefined || raw.trim() === '') {
    return undefined;
  }
  return raw;
}

export function setAuthCookie(
  cookies: AstroCookies,
  token: string,
  isSecure = false,
): void {
  cookies.set(AUTH_COOKIE_NAME, token, {
    httpOnly: true,
    sameSite: 'lax',
    path: '/',
    maxAge: COOKIE_MAX_AGE_SECONDS,
    secure: isSecure,
  });
}

export function clearAuthCookie(cookies: AstroCookies): void {
  cookies.delete(AUTH_COOKIE_NAME, {
    path: '/',
  });
}

export async function resolveServerSession(
  cookies: AstroCookies,
): Promise<ServerSession | null> {
  const token = getAuthToken(cookies);
  if (token === undefined) {
    return null;
  }

  try {
    const user = await getCurrentUser({ token });
    return { token, user };
  } catch (err) {
    if (err instanceof ApiError && err.status === 401) {
      clearAuthCookie(cookies);
    }
    return null;
  }
}
