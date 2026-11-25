<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\Waiter\CreateWaiterDto;
use App\DTOs\Waiter\ListWaitersDto;
use App\DTOs\Waiter\UpdateWaiterDto;
use App\Models\Waiter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface WaiterRepositoryInterface
{
    public function paginate(ListWaitersDto $dto): LengthAwarePaginator;

    public function findById(int $id): ?Waiter;

    public function existsByEmail(string $email, ?int $ignoreId = null): bool;

    public function create(CreateWaiterDto $dto): Waiter;

    public function update(Waiter $waiter, UpdateWaiterDto $dto): Waiter;

    public function delete(Waiter $waiter): void;
}
