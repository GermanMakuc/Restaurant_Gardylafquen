<?php

declare(strict_types=1);

namespace App\DTOs\Order;

final class ListOrdersDto
{
    private ?string $status;

    private ?int $tableId;

    private ?int $waiterId;

    private string $sort;

    private int $perPage;

    public function __construct(
        ?string $status = null,
        ?int $tableId = null,
        ?int $waiterId = null,
        string $sort = '-created_at',
        int $perPage = 15
    ) {
        $trimmedStatus = $status !== null ? trim($status) : null;
        $this->status = ($trimmedStatus !== null && $trimmedStatus !== '') ? $trimmedStatus : null;
        $this->tableId = ($tableId !== null && $tableId > 0) ? $tableId : null;
        $this->waiterId = ($waiterId !== null && $waiterId > 0) ? $waiterId : null;
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
        $tableId = isset($filter['table_id']) && is_numeric($filter['table_id']) ? (int) $filter['table_id'] : null;
        $waiterId = isset($filter['waiter_id']) && is_numeric($filter['waiter_id']) ? (int) $filter['waiter_id'] : null;
        $sort = isset($data['sort']) && is_string($data['sort']) ? $data['sort'] : '-created_at';
        $perPage = isset($data['per_page']) && is_numeric($data['per_page']) ? (int) $data['per_page'] : 15;

        return new self($status, $tableId, $waiterId, $sort, $perPage);
    }

    public function status(): ?string
    {
        return $this->status;
    }

    public function tableId(): ?int
    {
        return $this->tableId;
    }

    public function waiterId(): ?int
    {
        return $this->waiterId;
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
