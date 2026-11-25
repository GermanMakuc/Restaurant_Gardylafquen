<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\Table\CreateTableDto;
use App\DTOs\Table\ListTablesDto;
use App\DTOs\Table\UpdateTableDto;
use App\Models\Table;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TableRepositoryInterface
{
    public function paginate(ListTablesDto $dto): LengthAwarePaginator;

    public function findById(int $id): ?Table;

    public function findByNumber(int $number): ?Table;

    public function existsByNumber(int $number, ?int $ignoreId = null): bool;

    public function create(CreateTableDto $dto): Table;

    public function update(Table $table, UpdateTableDto $dto): Table;

    public function updateStatus(Table $table, string $status): Table;

    public function delete(Table $table): void;
}
