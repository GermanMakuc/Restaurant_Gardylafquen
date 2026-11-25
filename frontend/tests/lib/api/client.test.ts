import { afterEach, describe, expect, it, vi } from 'vitest';
import { ApiError, apiFetch } from '../../../src/lib/api/client';
import { getHealthStatus } from '../../../src/lib/api/health';

describe('apiFetch', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('sends JSON headers and bearer token when provided', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(JSON.stringify({ data: { ok: true } }), {
        status: 200,
        headers: { 'content-type': 'application/json' },
      }),
    );
    vi.stubGlobal('fetch', fetchMock);

    const result = await apiFetch<{ data: { ok: boolean } }>('/categories', {
      method: 'POST',
      body: JSON.stringify({ name: 'Bebidas' }),
      token: 'secret-token',
      baseUrl: 'http://localhost:8000/api/v1',
    });

    expect(result.data.ok).toBe(true);
    expect(fetchMock).toHaveBeenCalledWith(
      'http://localhost:8000/api/v1/categories',
      expect.objectContaining({
        method: 'POST',
        headers: expect.objectContaining({
          Accept: 'application/json',
          'Content-Type': 'application/json',
          Authorization: 'Bearer secret-token',
        }),
      }),
    );
  });

  it('throws ApiError with normalized backend error payload', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          message: 'Los datos enviados no son válidos.',
          code: 'validation_error',
          errors: { name: ['El campo nombre es obligatorio.'] },
        }),
        {
          status: 422,
          headers: { 'content-type': 'application/json' },
        },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    await expect(
      apiFetch('/categories', { baseUrl: 'http://localhost:8000/api/v1' }),
    ).rejects.toMatchObject({
      name: 'ApiError',
      status: 422,
      code: 'validation_error',
      errors: { name: ['El campo nombre es obligatorio.'] },
    } satisfies Partial<ApiError>);
  });
});

describe('getHealthStatus', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('unwraps data from /health response', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          data: {
            status: 'ok',
            service: 'restaurant-gardylafquen-api',
            database: 'ok',
            timestamp: '2026-11-24T21:00:00Z',
          },
        }),
        {
          status: 200,
          headers: { 'content-type': 'application/json' },
        },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    const status = await getHealthStatus('http://localhost:8000/api/v1');

    expect(status.status).toBe('ok');
    expect(status.database).toBe('ok');
  });
});
