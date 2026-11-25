import { afterEach, describe, expect, it, vi } from 'vitest';
import {
  createCategory,
  deleteCategory,
  listCategories,
  updateCategory,
} from '../../../src/lib/api/categories';

describe('categories API client', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('lists categories with filter, sort, and pagination query params', async () => {
    const payload = {
      data: [
        {
          id: 1,
          name: 'Bebidas',
          created_at: '2026-11-24T21:00:00Z',
          updated_at: '2026-11-24T21:00:00Z',
        },
      ],
      links: { first: null, last: null, prev: null, next: null },
      meta: {
        current_page: 1,
        from: 1,
        last_page: 1,
        path: 'http://localhost:8000/api/v1/categories',
        per_page: 15,
        to: 1,
        total: 1,
      },
    };

    const fetchMock = vi.fn().mockResolvedValue(
      new Response(JSON.stringify(payload), {
        status: 200,
        headers: { 'content-type': 'application/json' },
      }),
    );
    vi.stubGlobal('fetch', fetchMock);

    const result = await listCategories(
      { name: 'Bebidas', sort: 'name', page: 2, perPage: 10 },
      'http://localhost:8000/api/v1',
    );

    expect(result.data).toHaveLength(1);
    expect(fetchMock).toHaveBeenCalledWith(
      'http://localhost:8000/api/v1/categories?filter%5Bname%5D=Bebidas&sort=name&page=2&per_page=10',
      expect.any(Object),
    );
  });

  it('creates, updates, and deletes a category', async () => {
    const category = {
      id: 4,
      name: 'Postres',
      created_at: '2026-11-24T21:00:00Z',
      updated_at: '2026-11-24T21:00:00Z',
    };

    const fetchMock = vi
      .fn()
      .mockResolvedValueOnce(
        new Response(JSON.stringify({ data: category }), {
          status: 201,
          headers: { 'content-type': 'application/json' },
        }),
      )
      .mockResolvedValueOnce(
        new Response(
          JSON.stringify({ data: { ...category, name: 'Postres Caseros' } }),
          {
            status: 200,
            headers: { 'content-type': 'application/json' },
          },
        ),
      )
      .mockResolvedValueOnce(new Response(null, { status: 204 }));
    vi.stubGlobal('fetch', fetchMock);

    const created = await createCategory(
      { name: 'Postres' },
      'http://localhost:8000/api/v1',
    );
    expect(created.id).toBe(4);

    const updated = await updateCategory(
      4,
      { name: 'Postres Caseros' },
      'http://localhost:8000/api/v1',
    );
    expect(updated.name).toBe('Postres Caseros');

    await deleteCategory(4, 'http://localhost:8000/api/v1');
    expect(fetchMock).toHaveBeenCalledTimes(3);
  });
});
