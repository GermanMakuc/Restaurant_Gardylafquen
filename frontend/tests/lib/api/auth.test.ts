import { afterEach, describe, expect, it, vi } from 'vitest';
import {
  getCurrentUser,
  loginWithBackend,
  submitLogin,
} from '../../../src/lib/api/auth';
import { ApiError, resolveApiBaseUrl } from '../../../src/lib/api/client';

describe('resolveApiBaseUrl', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('uses the same-origin proxy path in browser environments', () => {
    vi.stubGlobal('window', {});
    expect(resolveApiBaseUrl()).toBe('/api/proxy');
  });
});

describe('auth API client', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('fetches authenticated user profile with bearer token', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          data: {
            id: 1,
            name: 'Administración',
            email: 'admin@gardylafquen.test',
            role: 'admin',
            created_at: '2026-11-24T22:00:00Z',
          },
        }),
        { status: 200, headers: { 'content-type': 'application/json' } },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    const user = await getCurrentUser({
      token: 'plain-token',
      baseUrl: 'http://localhost:8000/api/v1',
    });

    expect(user.role).toBe('admin');
    expect(fetchMock).toHaveBeenCalledWith(
      'http://localhost:8000/api/v1/auth/me',
      expect.objectContaining({
        headers: expect.objectContaining({
          Authorization: 'Bearer plain-token',
        }),
      }),
    );
  });

  it('logs in directly against backend API', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          data: {
            token: 'tok_123',
            token_type: 'Bearer',
            user: {
              id: 2,
              name: 'Caja',
              email: 'caja@gardylafquen.test',
              role: 'cashier',
              created_at: null,
            },
          },
        }),
        { status: 200, headers: { 'content-type': 'application/json' } },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    const session = await loginWithBackend(
      { email: 'caja@gardylafquen.test', password: 'secret' },
      'http://localhost:8000/api/v1',
    );

    expect(session.token).toBe('tok_123');
    expect(session.user.role).toBe('cashier');
  });

  it('maps failed submitLogin response to ApiError', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          message: 'Credenciales inválidas.',
          code: 'invalid_credentials',
        }),
        { status: 401, headers: { 'content-type': 'application/json' } },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    await expect(
      submitLogin({ email: 'bad@example.test', password: 'wrong' }),
    ).rejects.toMatchObject({
      name: 'ApiError',
      status: 401,
      code: 'invalid_credentials',
    } satisfies Partial<ApiError>);
  });
});
