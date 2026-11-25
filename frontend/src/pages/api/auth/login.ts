import type { APIRoute } from 'astro';
import { loginWithBackend } from '../../../lib/api/auth';
import { ApiError } from '../../../lib/api/client';
import { setAuthCookie } from '../../../lib/auth/session';
import { es } from '../../../lib/i18n/es';

function isJsonRequest(request: Request): boolean {
  const contentType = request.headers.get('content-type') ?? '';
  return contentType.includes('application/json');
}

function isSecureRequest(request: Request): boolean {
  const forwardedProto = request.headers.get('x-forwarded-proto');
  if (forwardedProto !== null) {
    return forwardedProto.split(',')[0]?.trim() === 'https';
  }
  return new URL(request.url).protocol === 'https:';
}

export const POST: APIRoute = async ({ request, cookies, redirect }) => {
  const wantsJson = isJsonRequest(request);
  let email = '';
  let password = '';

  if (wantsJson) {
    try {
      const body = (await request.json()) as Record<string, unknown>;
      email = typeof body['email'] === 'string' ? body['email'] : '';
      password = typeof body['password'] === 'string' ? body['password'] : '';
    } catch {
      return new Response(
        JSON.stringify({
          message: es.auth.unexpectedError,
          code: 'invalid_json',
        }),
        { status: 400, headers: { 'content-type': 'application/json' } },
      );
    }
  } else {
    const formData = await request.formData();
    const rawEmail = formData.get('email');
    const rawPassword = formData.get('password');
    email = typeof rawEmail === 'string' ? rawEmail : '';
    password = typeof rawPassword === 'string' ? rawPassword : '';
  }

  try {
    const result = await loginWithBackend({ email: email.trim(), password });
    setAuthCookie(cookies, result.token, isSecureRequest(request));

    if (!wantsJson) {
      return redirect('/', 303);
    }

    return new Response(JSON.stringify({ data: { user: result.user } }), {
      status: 200,
      headers: { 'content-type': 'application/json' },
    });
  } catch (err) {
    if (err instanceof ApiError) {
      if (!wantsJson) {
        return redirect('/login?error=invalid', 303);
      }

      return new Response(
        JSON.stringify({
          message: err.message,
          code: err.code,
          ...(err.errors !== undefined ? { errors: err.errors } : {}),
        }),
        { status: err.status, headers: { 'content-type': 'application/json' } },
      );
    }

    if (!wantsJson) {
      return redirect('/login?error=server', 303);
    }

    return new Response(
      JSON.stringify({
        message: es.auth.unexpectedError,
        code: 'server_error',
      }),
      { status: 500, headers: { 'content-type': 'application/json' } },
    );
  }
};
