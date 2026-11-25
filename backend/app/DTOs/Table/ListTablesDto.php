<?php

declare(strict_types=1);

namespace App\DTOs\Table;

final readonly class ListTablesDto
{
    public function __construct(
        public ?string $status = null,
        public string $sort = 'number',
        public int $perPage = 20,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : [];
        $rawStatus = isset($filter['status']) && is_string($filter['status']) ? trim($filter['status']) : null;
        $status = ($rawStatus !== null && $rawStatus !== '') ? $rawStatus : null;
        $sort = isset($data['sort']) && is_string($data['sort']) ? $data['sort'] : 'number';
        $rawPerPage = isset($data['per_page']) && is_numeric($data['per_page']) ? (int) $data['per_page'] : 20;

        return new self($status, $sort, max(1, min(100, $rawPerPage)));
    }
}
