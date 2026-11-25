<?php

declare(strict_types=1);

namespace App\DTOs\Table;

final class ListTablesDto
{
    private ?string $status;

    private string $sort;

    private int $perPage;

    public function __construct(
        ?string $status = null,
        string $sort = 'number',
        int $perPage = 20
    ) {
        $trimmed = $status !== null ? trim($status) : null;
        $this->status = ($trimmed !== null && $trimmed !== '') ? $trimmed : null;
        $this->sort = $sort;
        $this->perPage = max(1, min(100, $perPage));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : [];
        $status = isset($filter['status']) && is_string($filter['status']) ? $filter['status'] : null;
        $sort = isset($data['sort']) && is_string($data['sort']) ? $data['sort'] : 'number';
        $perPage = isset($data['per_page']) && is_numeric($data['per_page']) ? (int) $data['per_page'] : 20;

        return new self($status, $sort, $perPage);
    }

    public function status(): ?string
    {
        return $this->status;
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
