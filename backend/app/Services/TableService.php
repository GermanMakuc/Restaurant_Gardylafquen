<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Table\CreateTableDto;
use App\DTOs\Table\ListTablesDto;
use App\DTOs\Table\UpdateTableDto;
use App\Exceptions\DuplicateTableNumberException;
use App\Exceptions\TableNotFoundException;
use App\Models\Table;
use App\Repositories\Contracts\TableRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class TableService
{
    private TableRepositoryInterface $tables;

    public function __construct(TableRepositoryInterface $tables)
    {
        $this->tables = $tables;
    }

    public function list(ListTablesDto $dto): LengthAwarePaginator
    {
        return $this->tables->paginate($dto);
    }

    public function findOrFail(int $id): Table
    {
        $table = $this->tables->findById($id);

        if ($table === null) {
            throw new TableNotFoundException($id);
        }

        return $table;
    }

    public function create(CreateTableDto $dto): Table
    {
        if ($this->tables->existsByNumber($dto->number())) {
            throw new DuplicateTableNumberException($dto->number());
        }

        return $this->tables->create($dto);
    }

    public function update(int $id, UpdateTableDto $dto): Table
    {
        $table = $this->findOrFail($id);

        if ($this->tables->existsByNumber($dto->number(), $id)) {
            throw new DuplicateTableNumberException($dto->number());
        }

        return $this->tables->update($table, $dto);
    }

    public function delete(int $id): void
    {
        $table = $this->findOrFail($id);

        $this->tables->delete($table);
    }
}
