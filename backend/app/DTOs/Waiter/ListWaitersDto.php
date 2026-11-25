<?php

declare(strict_types=1);

namespace App\DTOs\Waiter;

final class ListWaitersDto
{
    private ?string $nameFilter;

    private ?bool $isActive;

    private string $sort;

    private int $perPage;

    public function __construct(
        ?string $nameFilter = null,
        ?bool $isActive = null,
        string $sort = '-created_at',
        int $perPage = 15
    ) {
        $trimmed = $nameFilter !== null ? trim($nameFilter) : null;
        $this->nameFilter = ($trimmed !== null && $trimmed !== '') ? $trimmed : null;
        $this->isActive = $isActive;
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

        $isActive = null;
        if (array_key_exists('is_active', $filter) && $filter['is_active'] !== null && $filter['is_active'] !== '') {
            $parsed = filter_var($filter['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $isActive = $parsed;
        }

        $sort = isset($data['sort']) && is_string($data['sort']) ? $data['sort'] : '-created_at';
        $perPage = isset($data['per_page']) && is_numeric($data['per_page']) ? (int) $data['per_page'] : 15;

        return new self($name, $isActive, $sort, $perPage);
    }

    public function nameFilter(): ?string
    {
        return $this->nameFilter;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
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
