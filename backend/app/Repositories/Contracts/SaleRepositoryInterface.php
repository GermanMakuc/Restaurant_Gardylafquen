<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\Sale\ListSalesDto;
use App\DTOs\Sale\SalesSummaryDto;
use App\Models\Sale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SaleRepositoryInterface
{
    public function paginate(ListSalesDto $dto): LengthAwarePaginator;

    public function summarize(ListSalesDto $dto): SalesSummaryDto;

    public function findById(int $id): ?Sale;

    public function existsForOrder(int $orderId): bool;

    public function create(
        int $orderId,
        int $tipPercent,
        int $subtotalCents,
        int $tipCents,
        int $totalCents,
        string $paidAt
    ): Sale;
}
