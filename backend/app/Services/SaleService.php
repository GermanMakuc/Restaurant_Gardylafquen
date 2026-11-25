<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Sale\CreateSaleDto;
use App\DTOs\Sale\ListSalesDto;
use App\DTOs\Sale\SalesSummaryDto;
use App\Enums\OrderStatus;
use App\Enums\TableStatus;
use App\Exceptions\OrderAlreadyPaidException;
use App\Exceptions\OrderNotEditableException;
use App\Exceptions\OrderNotFoundException;
use App\Exceptions\SaleNotFoundException;
use App\Models\Sale;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\TableRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class SaleService
{
    public function __construct(
        private readonly SaleRepositoryInterface $sales,
        private readonly OrderRepositoryInterface $orders,
        private readonly TableRepositoryInterface $tables,
    ) {
    }

    /**
     * @return LengthAwarePaginator<int, Sale>
     */
    public function list(ListSalesDto $dto): LengthAwarePaginator
    {
        return $this->sales->paginate($dto);
    }

    public function summary(ListSalesDto $dto): SalesSummaryDto
    {
        return $this->sales->summarize($dto);
    }

    public function findOrFail(int $id): Sale
    {
        $sale = $this->sales->findById($id);

        if ($sale === null) {
            throw new SaleNotFoundException($id);
        }

        return $sale;
    }

    public function create(CreateSaleDto $dto): Sale
    {
        $order = $this->orders->findById($dto->orderId);

        if ($order === null) {
            throw new OrderNotFoundException($dto->orderId);
        }

        if ($order->status === OrderStatus::PAID->value || $this->sales->existsForOrder((int) $order->id)) {
            throw new OrderAlreadyPaidException((int) $order->id);
        }

        if (!in_array($order->status, OrderStatus::activeValues(), true)) {
            throw new OrderNotEditableException((int) $order->id, $order->status);
        }

        $subtotalCents = (int) $order->subtotal_cents;
        $tipPercent = $dto->tipPercent;
        $tipCents = (int) round(($subtotalCents * $tipPercent) / 100);
        $totalCents = $subtotalCents + $tipCents;
        $paidAt = gmdate('Y-m-d H:i:s');

        /** @var Sale $sale */
        $sale = DB::transaction(function () use ($order, $tipPercent, $subtotalCents, $tipCents, $totalCents, $paidAt): Sale {
            $created = $this->sales->create(
                (int) $order->id,
                $tipPercent,
                $subtotalCents,
                $tipCents,
                $totalCents,
                $paidAt
            );

            $this->orders->updateStatus($order, OrderStatus::PAID->value);

            $table = $this->tables->findById((int) $order->table_id);
            if ($table !== null) {
                $this->tables->updateStatus($table, TableStatus::AVAILABLE->value);
            }

            return $created;
        });

        return $sale;
    }
}
