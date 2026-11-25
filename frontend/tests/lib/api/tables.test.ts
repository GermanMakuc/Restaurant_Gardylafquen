import { afterEach, describe, expect, it, vi } from 'vitest';
import {
  createTable,
  deleteTable,
  listTables,
  updateTable,
} from '../../../src/lib/api/tables';

describe('tables API client', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('lists tables with status filter and sort params', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          data: [],
          links: { first: null, last: null, prev: null, next: null },
          meta: {
            current_page: 1,
            from: null,
            last_page: 1,
            path: 'http://localhost:8000/api/v1/tables',
            per_page: 20,
            to: null,
            total: 0,
          },
        }),
        { status: 200, headers: { 'content-type': 'application/json' } },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    await listTables(
      { status: 'available', sort: 'number', page: 1, perPage: 20 },
      'http://localhost:8000/api/v1',
    );

    expect(fetchMock).toHaveBeenCalledWith(
      'http://localhost:8000/api/v1/tables?filter%5Bstatus%5D=available&sort=number&page=1&per_page=20',
      expect.any(Object),
    );
  });

  it('creates, updates, and deletes a table', async () => {
    const table = {
      id: 1,
      number: 5,
      label: 'Terraza',
      capacity: 4,
      status: 'available' as const,
      created_at: '2026-11-24T21:00:00Z',
      updated_at: '2026-11-24T21:00:00Z',
    };

    const fetchMock = vi
      .fn()
      .mockResolvedValueOnce(
        new Response(JSON.stringify({ data: table }), {
          status: 201,
          headers: { 'content-type': 'application/json' },
        }),
      )
      .mockResolvedValueOnce(
        new Response(
          JSON.stringify({ data: { ...table, status: 'reserved' } }),
          { status: 200, headers: { 'content-type': 'application/json' } },
        ),
      )
      .mockResolvedValueOnce(new Response(null, { status: 204 }));
    vi.stubGlobal('fetch', fetchMock);

    const created = await createTable(
      { number: 5, capacity: 4, status: 'available', label: 'Terraza' },
      'http://localhost:8000/api/v1',
    );
    expect(created.number).toBe(5);

    const updated = await updateTable(
      1,
      { number: 5, capacity: 4, status: 'reserved', label: 'Terraza' },
      'http://localhost:8000/api/v1',
    );
    expect(updated.status).toBe('reserved');

    await deleteTable(1, 'http://localhost:8000/api/v1');
    expect(fetchMock).toHaveBeenCalledTimes(3);
  });
});
