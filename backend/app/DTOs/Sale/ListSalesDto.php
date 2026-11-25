<?php

declare(strict_types=1);

namespace App\DTOs\Sale;

final class ListSalesDto
{
    private ?string $fromDate;

    private ?string $toDate;

    private string $sort;

    private int $perPage;

    public function __construct(
        ?string $fromDate = null,
        ?string $toDate = null,
        string $sort = '-paid_at',
        int $perPage = 15
    ) {
        $this->fromDate = ($fromDate !== null && trim($fromDate) !== '') ? trim($fromDate) : null;
        $this->toDate = ($toDate !== null && trim($toDate) !== '') ? trim($toDate) : null;
        $this->sort = $sort;
        $this->perPage = max(1, min(100, $perPage));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $filter = isset($data['filter']) && is_array($data['filter']) ? $data['filter'] : [];
        $from = isset($filter['from']) && is_string($filter['from']) ? $filter['from'] : null;
        $to = isset($filter['to']) && is_string($filter['to']) ? $filter['to'] : null;
        $sort = isset($data['sort']) && is_string($data['sort']) ? $data['sort'] : '-paid_at';
        $perPage = isset($data['per_page']) && is_numeric($data['per_page']) ? (int) $data['per_page'] : 15;

        return new self($from, $to, $sort, $perPage);
    }

    public function fromDate(): ?string
    {
        return $this->fromDate;
    }

    public function toDate(): ?string
    {
        return $this->toDate;
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
