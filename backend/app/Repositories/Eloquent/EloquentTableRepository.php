<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\Table\CreateTableDto;
use App\DTOs\Table\ListTablesDto;
use App\DTOs\Table\UpdateTableDto;
use App\Models\Table;
use App\Repositories\Contracts\TableRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final readonly class EloquentTableRepository implements TableRepositoryInterface
{
    /** @var array<string, array{0: string, 1: string}> */
    private const SORT_MAP = [
        'id' => ['id', 'asc'],
        '-id' => ['id', 'desc'],
        'number' => ['number', 'asc'],
        '-number' => ['number', 'desc'],
        'capacity' => ['capacity', 'asc'],
        '-capacity' => ['capacity', 'desc'],
        'created_at' => ['created_at', 'asc'],
        '-created_at' => ['created_at', 'desc'],
    ];

    public function __construct(private Table $model)
    {
    }

    /**
     * @return LengthAwarePaginator<int, Table>
     */
    public function paginate(ListTablesDto $dto): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if ($dto->status !== null) {
            $query->where('status', $dto->status);
        }

        $sortConfig = self::SORT_MAP[$dto->sort] ?? self::SORT_MAP['number'];
        $query->orderBy($sortConfig[0], $sortConfig[1]);

        if ($sortConfig[0] !== 'id') {
            $query->orderBy('id', 'asc');
        }

        return $query->paginate($dto->perPage);
    }

    public function findById(int $id): ?Table
    {
        /** @var Table|null $table */
        $table = $this->model->newQuery()->find($id);

        return $table;
    }

    public function findByNumber(int $number): ?Table
    {
        /** @var Table|null $table */
        $table = $this->model->newQuery()->where('number', $number)->first();

        return $table;
    }

    public function existsByNumber(int $number, ?int $ignoreId = null): bool
    {
        $query = $this->model->newQuery()
            ->withoutGlobalScope(SoftDeletingScope::class)
            ->where('number', $number);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public function create(CreateTableDto $dto): Table
    {
        /** @var Table $table */
        $table = $this->model->newQuery()->create([
            'number' => $dto->number,
            'label' => $dto->label,
            'capacity' => $dto->capacity,
            'status' => $dto->status,
        ]);

        return $table;
    }

    public function update(Table $table, UpdateTableDto $dto): Table
    {
        $table->update([
            'number' => $dto->number,
            'label' => $dto->label,
            'capacity' => $dto->capacity,
            'status' => $dto->status,
        ]);

        /** @var Table $fresh */
        $fresh = $table->fresh();

        return $fresh;
    }

    public function updateStatus(Table $table, string $status): Table
    {
        $table->update(['status' => $status]);

        /** @var Table $fresh */
        $fresh = $table->fresh();

        return $fresh;
    }

    public function delete(Table $table): void
    {
        $table->delete();
    }
}
