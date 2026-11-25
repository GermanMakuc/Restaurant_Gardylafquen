import { afterEach, describe, expect, it, vi } from 'vitest';
import {
  createProduct,
  deleteProduct,
  listProducts,
  updateProduct,
  uploadProductImage,
} from '../../../src/lib/api/products';

describe('products API client', () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it('lists products with name, category_id, sort, and pagination params', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          data: [],
          links: { first: null, last: null, prev: null, next: null },
          meta: {
            current_page: 1,
            from: null,
            last_page: 1,
            path: 'http://localhost:8000/api/v1/products',
            per_page: 15,
            to: null,
            total: 0,
          },
        }),
        { status: 200, headers: { 'content-type': 'application/json' } },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    await listProducts(
      { name: 'Salmón', categoryId: 2, sort: '-price_cents', page: 1, perPage: 15 },
      'http://localhost:8000/api/v1',
    );

    expect(fetchMock).toHaveBeenCalledWith(
      'http://localhost:8000/api/v1/products?filter%5Bname%5D=Salm%C3%B3n&filter%5Bcategory_id%5D=2&sort=-price_cents&page=1&per_page=15',
      expect.any(Object),
    );
  });

  it('creates, updates, uploads image via FormData, and deletes a product', async () => {
    const product = {
      id: 7,
      name: 'Salmón',
      description: 'A la plancha',
      category_id: 2,
      price_cents: 1290000,
      image_url: null,
      created_at: '2026-11-24T21:00:00Z',
      updated_at: '2026-11-24T21:00:00Z',
    };

    const fetchMock = vi
      .fn()
      .mockResolvedValueOnce(
        new Response(JSON.stringify({ data: product }), {
          status: 201,
          headers: { 'content-type': 'application/json' },
        }),
      )
      .mockResolvedValueOnce(
        new Response(
          JSON.stringify({ data: { ...product, name: 'Salmón Austral' } }),
          { status: 200, headers: { 'content-type': 'application/json' } },
        ),
      )
      .mockResolvedValueOnce(
        new Response(
          JSON.stringify({
            data: {
              ...product,
              image_url: 'http://localhost:8000/storage/products/7-abc.jpg',
            },
          }),
          { status: 200, headers: { 'content-type': 'application/json' } },
        ),
      )
      .mockResolvedValueOnce(new Response(null, { status: 204 }));
    vi.stubGlobal('fetch', fetchMock);

    const created = await createProduct(
      {
        name: 'Salmón',
        category_id: 2,
        price_cents: 1290000,
        description: 'A la plancha',
      },
      'http://localhost:8000/api/v1',
    );
    expect(created.id).toBe(7);

    const updated = await updateProduct(
      7,
      {
        name: 'Salmón Austral',
        category_id: 2,
        price_cents: 1290000,
        description: 'A la plancha',
      },
      'http://localhost:8000/api/v1',
    );
    expect(updated.name).toBe('Salmón Austral');

    const file = new File(['img'], 'salmon.jpg', { type: 'image/jpeg' });
    const withImage = await uploadProductImage(
      7,
      file,
      'http://localhost:8000/api/v1',
    );
    expect(withImage.image_url).toContain('products/7-abc.jpg');

    await deleteProduct(7, 'http://localhost:8000/api/v1');
    expect(fetchMock).toHaveBeenCalledTimes(4);
  });
});
