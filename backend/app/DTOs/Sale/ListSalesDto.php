<?php

declare(strict_types=1);

namespace App\DTOs\Sale;

final readonly class ListSalesDto
{
    public function __construct(
        public ?string $fromDate = null,
        public ?string $toDate = null,
        public string $sort = '-paid_at',
        public int $perPage = 15,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : [];
        $rawFrom = isset($filter['from']) && is_string($filter['from']) ? trim($filter['from']) : null;
        $fromDate = ($rawFrom !== null && $rawFrom !== '') ? $rawFrom : null;
        $rawTo = isset($filter['to']) && is_string($filter['to']) ? trim($filter['to']) : null;
        $toDate = ($rawTo !== null && $rawTo !== '') ? $rawTo : null;
        $sort = isset($data['sort']) && is_string($data['sort']) ? $data['sort'] : '-paid_at';
        $rawPerPage = isset($data['per_page']) && is_numeric($data['per_page']) ? (int) $data['per_page'] : 15;

        return new self($fromDate, $toDate, $sort, max(1, min(100, $rawPerPage)));
    }
}
