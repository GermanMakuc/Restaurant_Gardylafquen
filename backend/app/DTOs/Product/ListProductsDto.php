<?php

declare(strict_types=1);

namespace App\DTOs\Product;

final class ListProductsDto
{
    private ?string $nameFilter;

    private ?int $categoryId;

    private string $sort;

    private int $perPage;

    public function __construct(
        ?string $nameFilter = null,
        ?int $categoryId = null,
        string $sort = '-created_at',
        int $perPage = 15
    ) {
        $trimmed = $nameFilter !== null ? trim($nameFilter) : null;
        $this->nameFilter = ($trimmed !== null && $trimmed !== '') ? $trimmed : null;
        $this->categoryId = ($categoryId !== null && $categoryId > 0) ? $categoryId : null;
        $this->sort = $sort;
        $this->perPage = max(1, min(100, $perPage));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : [];
        $name = isset($filter['name']) && is_string($filter['name']) ? $filter['name'] : null;
        $categoryId = isset($filter['category_id']) && is_numeric($filter['category_id'])
            ? (int) $filter['category_id']
            : null;
        $sort = isset($data['sort']) && is_string($data['sort']) ? $data['sort'] : '-created_at';
        $perPage = isset($data['per_page']) && is_numeric($data['per_page']) ? (int) $data['per_page'] : 15;

        return new self($name, $categoryId, $sort, $perPage);
    }

    public function nameFilter(): ?string
    {
        return $this->nameFilter;
    }

    public function categoryId(): ?int
    {
        return $this->categoryId;
    }

    public function sort(): string
    {
        return $this->sort;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }
}
