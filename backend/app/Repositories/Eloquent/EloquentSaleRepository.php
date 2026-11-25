<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\Sale\ListSalesDto;
use App\DTOs\Sale\SalesSummaryDto;
use App\Models\Sale;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class EloquentSaleRepository implements SaleRepositoryInterface
{
    /** @var array<string, array{0: string, 1: string}> */
    private const SORT_MAP = [
        'id' => ['id', 'asc'],
        '-id' => ['id', 'desc'],
        'paid_at' => ['paid_at', 'asc'],
        '-paid_at' => ['paid_at', 'desc'],
        'total_cents' => ['total_cents', 'asc'],
        '-total_cents' => ['total_cents', 'desc'],
    ];

    private Sale $model;

    public function __construct(Sale $model)
    {
        $this->model = $model;
    }

    public function paginate(ListSalesDto $dto): LengthAwarePaginator
    {
        $query = $this->applyDateFilters($this->model->newQuery(), $dto)
            ->with(['order.table', 'order.waiter', 'order.items']);

        $sortConfig = self::SORT_MAP[$dto->sort()] ?? self::SORT_MAP['-paid_at'];
        $query->orderBy($sortConfig[0], $sortConfig[1]);

        if ($sortConfig[0] !== 'id') {
            $query->orderBy('id', 'desc');
        }

        return $query->paginate($dto->perPage());
    }

    public function summarize(ListSalesDto $dto): SalesSummaryDto
    {
        $query = $this->applyDateFilters($this->model->newQuery(), $dto);

        /** @var object{aggregate_count?: mixed, aggregate_subtotal?: mixed, aggregate_tip?: mixed, aggregate_total?: mixed}|null $row */
        $row = $query->selectRaw(
            'COUNT(*) as aggregate_count, COALESCE(SUM(subtotal_cents), 0) as aggregate_subtotal, COALESCE(SUM(tip_cents), 0) as aggregate_tip, COALESCE(SUM(total_cents), 0) as aggregate_total'
        )->first();

        if ($row === null) {
            return new SalesSummaryDto(0, 0, 0, 0);
        }

        return new SalesSummaryDto(
            (int) ($row->aggregate_count ?? 0),
            (int) ($row->aggregate_subtotal ?? 0),
            (int) ($row->aggregate_tip ?? 0),
            (int) ($row->aggregate_total ?? 0)
        );
    }

    public function findById(int $id): ?Sale
    {
        /** @var Sale|null $sale */
        $sale = $this->model->newQuery()
            ->with(['order.table', 'order.waiter', 'order.items'])
            ->find($id);

        return $sale;
    }

    public function existsForOrder(int $orderId): bool
    {
        return $this->model->newQuery()->where('order_id', $orderId)->exists();
    }

    public function create(
        int $orderId,
        int $tipPercent,
        int $subtotalCents,
        int $tipCents,
        int $totalCents,
        string $paidAt
    ): Sale {
        /** @var Sale $sale */
        $sale = $this->model->newQuery()->create([
            'order_id' => $orderId,
            'tip_percent' => $tipPercent,
            'subtotal_cents' => $subtotalCents,
            'tip_cents' => $tipCents,
            'total_cents' => $totalCents,
            'paid_at' => $paidAt,
        ]);

        /** @var Sale $fresh */
        $fresh = $sale->fresh(['order.table', 'order.waiter', 'order.items']);

        return $fresh;
    }

    private function applyDateFilters(Builder $query, ListSalesDto $dto): Builder
    {
        if ($dto->fromDate() !== null) {
            $query->where('paid_at', '>=', $dto->fromDate() . ' 00:00:00');
        }

        if ($dto->toDate() !== null) {
            $query->where('paid_at', '<=', $dto->toDate() . ' 23:59:59');
        }

        return $query;
    }
}
