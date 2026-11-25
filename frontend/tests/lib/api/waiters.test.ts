import { afterEach, describe, expect, it, vi } from 'vitest';
import {
  createWaiter,
  deleteWaiter,
  listWaiters,
  updateWaiter,
} from '../../../src/lib/api/waiters';

describe('waiters API client', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('lists waiters with name, is_active, and pagination params', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          data: [],
          links: { first: null, last: null, prev: null, next: null },
          meta: {
            current_page: 1,
            from: null,
            last_page: 1,
            path: 'http://localhost:8000/api/v1/waiters',
            per_page: 15,
            to: null,
            total: 0,
          },
        }),
        { status: 200, headers: { 'content-type': 'application/json' } },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    await listWaiters(
      { name: 'Camila', isActive: true, sort: 'name', page: 1, perPage: 15 },
      'http://localhost:8000/api/v1',
    );

    expect(fetchMock).toHaveBeenCalledWith(
      'http://localhost:8000/api/v1/waiters?filter%5Bname%5D=Camila&filter%5Bis_active%5D=true&sort=name&page=1&per_page=15',
      expect.any(Object),
    );
  });

  it('creates, updates, and deletes a waiter', async () => {
    const waiter = {
      id: 3,
      name: 'Camila Rojas',
      email: 'camila@example.test',
      is_active: true,
      created_at: '2026-11-24T21:00:00Z',
      updated_at: '2026-11-24T21:00:00Z',
    };

    const fetchMock = vi
      .fn()
      .mockResolvedValueOnce(
        new Response(JSON.stringify({ data: waiter }), {
          status: 201,
          headers: { 'content-type': 'application/json' },
        }),
      )
      .mockResolvedValueOnce(
        new Response(
          JSON.stringify({ data: { ...waiter, is_active: false } }),
          { status: 200, headers: { 'content-type': 'application/json' } },
        ),
      )
      .mockResolvedValueOnce(new Response(null, { status: 204 }));
    vi.stubGlobal('fetch', fetchMock);

    const created = await createWaiter(
      { name: 'Camila Rojas', email: 'camila@example.test', is_active: true },
      'http://localhost:8000/api/v1',
    );
    expect(created.id).toBe(3);

    const updated = await updateWaiter(
      3,
      { name: 'Camila Rojas', email: 'camila@example.test', is_active: false },
      'http://localhost:8000/api/v1',
    );
    expect(updated.is_active).toBe(false);

    await deleteWaiter(3, 'http://localhost:8000/api/v1');
    expect(fetchMock).toHaveBeenCalledTimes(3);
  });
});
