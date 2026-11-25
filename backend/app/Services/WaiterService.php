<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Waiter\CreateWaiterDto;
use App\DTOs\Waiter\ListWaitersDto;
use App\DTOs\Waiter\UpdateWaiterDto;
use App\Exceptions\DuplicateWaiterEmailException;
use App\Exceptions\WaiterNotFoundException;
use App\Models\Waiter;
use App\Repositories\Contracts\WaiterRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class WaiterService
{
    private WaiterRepositoryInterface $waiters;

    public function __construct(WaiterRepositoryInterface $waiters)
    {
        $this->waiters = $waiters;
    }

    public function list(ListWaitersDto $dto): LengthAwarePaginator
    {
        return $this->waiters->paginate($dto);
    }

    public function findOrFail(int $id): Waiter
    {
        $waiter = $this->waiters->findById($id);

        if ($waiter === null) {
            throw new WaiterNotFoundException($id);
        }

        return $waiter;
    }

    public function create(CreateWaiterDto $dto): Waiter
    {
        if ($this->waiters->existsByEmail($dto->email())) {
            throw new DuplicateWaiterEmailException($dto->email());
        }

        return $this->waiters->create($dto);
    }

    public function update(int $id, UpdateWaiterDto $dto): Waiter
    {
        $waiter = $this->findOrFail($id);

        if ($this->waiters->existsByEmail($dto->email(), $id)) {
            throw new DuplicateWaiterEmailException($dto->email());
        }

        return $this->waiters->update($waiter, $dto);
    }

    public function delete(int $id): void
    {
        $waiter = $this->findOrFail($id);

        $this->waiters->delete($waiter);
    }
}
