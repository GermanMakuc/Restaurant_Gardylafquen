<?php

declare(strict_types=1);

namespace App\DTOs\Order;

final readonly class ListOrdersDto
{
    public function __construct(
        public ?string $status = null,
        public ?int $tableId = null,
        public ?int $waiterId = null,
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
        $rawStatus = isset($filter['status']) && is_string($filter['status']) ? trim($filter['status']) : null;
        $status = ($rawStatus !== null && $rawStatus !== '') ? $rawStatus : null;
        $rawTableId = isset($filter['table_id']) && is_numeric($filter['table_id']) ? (int) $filter['table_id'] : null;
        $tableId = ($rawTableId !== null && $rawTableId > 0) ? $rawTableId : null;
        $rawWaiterId = isset($filter['waiter_id']) && is_numeric($filter['waiter_id']) ? (int) $filter['waiter_id'] : null;
        $waiterId = ($rawWaiterId !== null && $rawWaiterId > 0) ? $rawWaiterId : null;
        $sort = isset($data['sort']) && is_string($data['sort']) ? $data['sort'] : '-created_at';
        $rawPerPage = isset($data['per_page']) && is_numeric($data['per_page']) ? (int) $data['per_page'] : 15;

        return new self($status, $tableId, $waiterId, $sort, max(1, min(100, $rawPerPage)));
    }
}
