import { afterEach, describe, expect, it, vi } from 'vitest';
import {
  createSale,
  getSalesSummary,
  listSales,
} from '../../../src/lib/api/sales';

describe('sales API client', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('lists sales and fetches summary with date filters', async () => {
    const fetchMock = vi
      .fn()
      .mockResolvedValueOnce(
        new Response(
          JSON.stringify({
            data: [],
            links: { first: null, last: null, prev: null, next: null },
            meta: {
              current_page: 1,
              from: null,
              last_page: 1,
              path: 'http://localhost:8000/api/v1/sales',
              per_page: 15,
              to: null,
              total: 0,
            },
          }),
          { status: 200, headers: { 'content-type': 'application/json' } },
        ),
      )
      .mockResolvedValueOnce(
        new Response(
          JSON.stringify({
            data: {
              count: 2,
              subtotal_cents: 1000000,
              tip_cents: 100000,
              total_cents: 1100000,
            },
          }),
          { status: 200, headers: { 'content-type': 'application/json' } },
        ),
      );
    vi.stubGlobal('fetch', fetchMock);

    await listSales(
      { from: '2026-11-01', to: '2026-11-30', sort: '-paid_at', page: 1, perPage: 15 },
      'http://localhost:8000/api/v1',
    );
    const summary = await getSalesSummary(
      { from: '2026-11-01', to: '2026-11-30' },
      'http://localhost:8000/api/v1',
    );

    expect(summary.total_cents).toBe(1100000);
    expect(fetchMock).toHaveBeenNthCalledWith(
      1,
      'http://localhost:8000/api/v1/sales?filter%5Bfrom%5D=2026-11-01&filter%5Bto%5D=2026-11-30&sort=-paid_at&page=1&per_page=15',
      expect.any(Object),
    );
    expect(fetchMock).toHaveBeenNthCalledWith(
      2,
      'http://localhost:8000/api/v1/sales/summary?filter%5Bfrom%5D=2026-11-01&filter%5Bto%5D=2026-11-30',
      expect.any(Object),
    );
  });

  it('creates a sale for an order', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          data: {
            id: 5,
            order_id: 3,
            tip_percent: 10,
            subtotal_cents: 500000,
            tip_cents: 50000,
            total_cents: 550000,
            paid_at: '2026-11-24T22:00:00Z',
            created_at: '2026-11-24T22:00:00Z',
          },
        }),
        { status: 201, headers: { 'content-type': 'application/json' } },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    const sale = await createSale(
      { order_id: 3, tip_percent: 10 },
      'http://localhost:8000/api/v1',
    );

    expect(sale.total_cents).toBe(550000);
  });
});
