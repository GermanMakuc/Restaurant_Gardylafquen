<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\Waiter\CreateWaiterDto;
use App\DTOs\Waiter\ListWaitersDto;
use App\DTOs\Waiter\UpdateWaiterDto;
use App\Models\Waiter;
use App\Repositories\Contracts\WaiterRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final readonly class EloquentWaiterRepository implements WaiterRepositoryInterface
{
    /** @var array<string, array{0: string, 1: string}> */
    private const SORT_MAP = [
        'id' => ['id', 'asc'],
        '-id' => ['id', 'desc'],
        'name' => ['name', 'asc'],
        '-name' => ['name', 'desc'],
        'created_at' => ['created_at', 'asc'],
        '-created_at' => ['created_at', 'desc'],
    ];

    public function __construct(private Waiter $model)
    {
    }

    /**
     * @return LengthAwarePaginator<int, Waiter>
     */
    public function paginate(ListWaitersDto $dto): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if ($dto->nameFilter() !== null) {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $dto->nameFilter());
            $query->where('name', 'like', '%' . $escaped . '%');
        }

        if ($dto->isActive() !== null) {
            $query->where('is_active', $dto->isActive());
        }

        $sortConfig = self::SORT_MAP[$dto->sort()] ?? self::SORT_MAP['-created_at'];
        $query->orderBy($sortConfig[0], $sortConfig[1]);

        if ($sortConfig[0] !== 'id') {
            $query->orderBy('id', 'desc');
        }

        return $query->paginate($dto->perPage());
    }

    public function findById(int $id): ?Waiter
    {
        /** @var Waiter|null $waiter */
        $waiter = $this->model->newQuery()->find($id);

        return $waiter;
    }

    public function existsByEmail(string $email, ?int $ignoreId = null): bool
    {
        $query = $this->model->newQuery()
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($email)]);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public function create(CreateWaiterDto $dto): Waiter
    {
        /** @var Waiter $waiter */
        $waiter = $this->model->newQuery()->create([
            'name' => $dto->name(),
            'email' => $dto->email(),
            'is_active' => $dto->isActive(),
        ]);

        return $waiter;
    }

    public function update(Waiter $waiter, UpdateWaiterDto $dto): Waiter
    {
        $waiter->update([
            'name' => $dto->name(),
            'email' => $dto->email(),
            'is_active' => $dto->isActive(),
        ]);

        /** @var Waiter $fresh */
        $fresh = $waiter->fresh();

        return $fresh;
    }

    public function delete(Waiter $waiter): void
    {
        $waiter->delete();
    }
}
