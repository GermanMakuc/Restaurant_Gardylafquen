<?php

declare(strict_types=1);

namespace App\DTOs\Product;

final readonly class ListProductsDto
{
    public function __construct(
        public ?string $nameFilter = null,
        public ?int $categoryId = null,
        public string $sort = '-created_at',
        public int $perPage = 15,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : [];
        $rawName = isset($filter['name']) && is_string($filter['name']) ? trim($filter['name']) : null;
        $name = ($rawName !== null && $rawName !== '') ? $rawName : null;
        $rawCategoryId = isset($filter['category_id']) && is_numeric($filter['category_id'])
            ? (int) $filter['category_id']
            : null;
        $categoryId = ($rawCategoryId !== null && $rawCategoryId > 0) ? $rawCategoryId : null;
        $sort = isset($data['sort']) && is_string($data['sort']) ? $data['sort'] : '-created_at';
        $rawPerPage = isset($data['per_page']) && is_numeric($data['per_page']) ? (int) $data['per_page'] : 15;

        return new self($name, $categoryId, $sort, max(1, min(100, $rawPerPage)));
    }
}
