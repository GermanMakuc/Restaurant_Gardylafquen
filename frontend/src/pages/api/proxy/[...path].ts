import type { APIRoute } from 'astro';
import { resolveApiBaseUrl } from '../../../lib/api/client';
import { clearAuthCookie, getAuthToken } from '../../../lib/auth/session';
import { es } from '../../../lib/i18n/es';

export const ALL: APIRoute = async ({ params, request, url, cookies }) => {
  const token = getAuthToken(cookies);
  if (token === undefined) {
    return new Response(
      JSON.stringify({
        message: es.auth.sessionExpired,
        code: 'unauthenticated',
      }),
      {
        status: 401,
        headers: { 'content-type': 'application/json' },
      },
    );
  }

  const rawPath = (params['path'] ?? '').replace(/^\/+/, '');
  if (rawPath === '' || rawPath.includes('..')) {
    return new Response(
      JSON.stringify({
        message: es.auth.unexpectedError,
        code: 'invalid_path',
      }),
      {
        status: 400,
        headers: { 'content-type': 'application/json' },
      },
    );
  }

  const targetUrl = `${resolveApiBaseUrl()}/${rawPath}${url.search}`;
  const headers = new Headers();
  headers.set('Accept', 'application/json');
  headers.set('Authorization', `Bearer ${token}`);

  const contentType = request.headers.get('content-type');
  let body: ArrayBuffer | undefined;

  if (request.method !== 'GET' && request.method !== 'HEAD') {
    if (contentType !== null) {
      headers.set('Content-Type', contentType);
    }
    const buffer = await request.arrayBuffer();
    if (buffer.byteLength > 0) {
      body = buffer;
    }
  }

  try {
    const backendResponse = await fetch(targetUrl, {
      method: request.method,
      headers,
      ...(body !== undefined ? { body } : {}),
    });

    if (backendResponse.status === 401) {
      clearAuthCookie(cookies);
    }

    if (backendResponse.status === 204) {
      return new Response(null, { status: 204 });
    }

    const responseHeaders = new Headers();
    const responseType = backendResponse.headers.get('content-type');
    if (responseType !== null) {
      responseHeaders.set('content-type', responseType);
    }
    const location = backendResponse.headers.get('location');
    if (location !== null) {
      responseHeaders.set('location', location);
    }

    const responseBuffer = await backendResponse.arrayBuffer();
    return new Response(responseBuffer, {
      status: backendResponse.status,
      headers: responseHeaders,
    });
  } catch {
    return new Response(
      JSON.stringify({
        message: es.auth.unexpectedError,
        code: 'proxy_error',
      }),
      {
        status: 502,
        headers: { 'content-type': 'application/json' },
      },
    );
  }
};
