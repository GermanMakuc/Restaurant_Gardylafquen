import type { APIRoute } from 'astro';
import { logoutFromBackend } from '../../../lib/api/auth';
import { clearAuthCookie, getAuthToken } from '../../../lib/auth/session';

export const POST: APIRoute = async ({ request, cookies, redirect }) => {
  const token = getAuthToken(cookies);

  if (token !== undefined) {
    try {
      await logoutFromBackend(token);
    } catch {
      // Clear local session cookie even if backend token already expired
    }
  }

  clearAuthCookie(cookies);

  const accept = request.headers.get('accept') ?? '';
  if (accept.includes('application/json') && !accept.includes('text/html')) {
    return new Response(null, { status: 204 });
  }

  return redirect('/login', 303);
};
