import { afterEach, describe, expect, it, vi } from 'vitest';
import {
  createOrder,
  deleteOrder,
  listOrders,
  updateOrder,
  updateOrderStatus,
} from '../../../src/lib/api/orders';

describe('orders API client', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('lists orders with status filter and pagination params', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          data: [],
          links: { first: null, last: null, prev: null, next: null },
          meta: {
            current_page: 1,
            from: null,
            last_page: 1,
            path: 'http://localhost:8000/api/v1/orders',
            per_page: 15,
            to: null,
            total: 0,
          },
        }),
        { status: 200, headers: { 'content-type': 'application/json' } },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    await listOrders(
      { status: 'waiting', sort: '-created_at', page: 1, perPage: 15 },
      'http://localhost:8000/api/v1',
    );

    expect(fetchMock).toHaveBeenCalledWith(
      'http://localhost:8000/api/v1/orders?filter%5Bstatus%5D=waiting&sort=-created_at&page=1&per_page=15',
      expect.any(Object),
    );
  });

  it('creates, updates, transitions status, and deletes an order', async () => {
    const order = {
      id: 1,
      table_id: 2,
      waiter_id: 1,
      status: 'waiting' as const,
      notes: null,
      subtotal_cents: 350000,
      items: [],
      created_at: '2026-11-24T21:00:00Z',
      updated_at: '2026-11-24T21:00:00Z',
    };

    const fetchMock = vi
      .fn()
      .mockResolvedValueOnce(
        new Response(JSON.stringify({ data: order }), {
          status: 201,
          headers: { 'content-type': 'application/json' },
        }),
      )
      .mockResolvedValueOnce(
        new Response(
          JSON.stringify({ data: { ...order, subtotal_cents: 700000 } }),
          { status: 200, headers: { 'content-type': 'application/json' } },
        ),
      )
      .mockResolvedValueOnce(
        new Response(
          JSON.stringify({ data: { ...order, status: 'served' } }),
          { status: 200, headers: { 'content-type': 'application/json' } },
        ),
      )
      .mockResolvedValueOnce(new Response(null, { status: 204 }));
    vi.stubGlobal('fetch', fetchMock);

    const created = await createOrder(
      {
        table_id: 2,
        waiter_id: 1,
        notes: null,
        items: [{ product_id: 1, quantity: 1 }],
      },
      'http://localhost:8000/api/v1',
    );
    expect(created.id).toBe(1);

    const updated = await updateOrder(
      1,
      {
        table_id: 2,
        waiter_id: 1,
        notes: null,
        items: [{ product_id: 1, quantity: 2 }],
      },
      'http://localhost:8000/api/v1',
    );
    expect(updated.subtotal_cents).toBe(700000);

    const served = await updateOrderStatus(
      1,
      'served',
      'http://localhost:8000/api/v1',
    );
    expect(served.status).toBe('served');

    await deleteOrder(1, 'http://localhost:8000/api/v1');
    expect(fetchMock).toHaveBeenCalledTimes(4);
  });
});
