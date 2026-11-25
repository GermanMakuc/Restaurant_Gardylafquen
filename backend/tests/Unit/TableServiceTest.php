<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTOs\Table\CreateTableDto;
use App\DTOs\Table\ListTablesDto;
use App\DTOs\Table\UpdateTableDto;
use App\Enums\TableStatus;
use App\Exceptions\DuplicateTableNumberException;
use App\Exceptions\TableNotFoundException;
use App\Models\Table;
use App\Repositories\Contracts\TableRepositoryInterface;
use App\Services\TableService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TableServiceTest extends TestCase
{
    public function test_list_delegates_to_repository(): void
    {
        $dto = new ListTablesDto(TableStatus::AVAILABLE, 'number', 20);
        /** @var LengthAwarePaginator&MockObject $paginator */
        $paginator = $this->createMock(LengthAwarePaginator::class);

        /** @var TableRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(TableRepositoryInterface::class);
        $repository->expects($this->once())->method('paginate')->with($dto)->willReturn($paginator);

        $service = new TableService($repository);

        $this->assertSame($paginator, $service->list($dto));
    }

    public function test_find_or_fail_throws_when_missing(): void
    {
        /** @var TableRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(TableRepositoryInterface::class);
        $repository->expects($this->once())->method('findById')->with(55)->willReturn(null);

        $service = new TableService($repository);

        $this->expectException(TableNotFoundException::class);
        $service->findOrFail(55);
    }

    public function test_create_persists_table_when_number_is_unique(): void
    {
        $dto = new CreateTableDto(1, 4, TableStatus::AVAILABLE, 'Ventana');
        $created = new Table(['number' => 1, 'capacity' => 4, 'status' => TableStatus::AVAILABLE, 'label' => 'Ventana']);
        $created->id = 1;

        /** @var TableRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(TableRepositoryInterface::class);
        $repository->expects($this->once())->method('existsByNumber')->with(1, null)->willReturn(false);
        $repository->expects($this->once())->method('create')->with($dto)->willReturn($created);

        $service = new TableService($repository);

        $this->assertSame($created, $service->create($dto));
    }

    public function test_create_throws_when_number_already_exists(): void
    {
        $dto = new CreateTableDto(1, 4);

        /** @var TableRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(TableRepositoryInterface::class);
        $repository->expects($this->once())->method('existsByNumber')->with(1, null)->willReturn(true);
        $repository->expects($this->never())->method('create');

        $service = new TableService($repository);

        $this->expectException(DuplicateTableNumberException::class);
        $service->create($dto);
    }

    public function test_update_modifies_table_when_found_and_number_unique(): void
    {
        $existing = new Table(['number' => 2, 'capacity' => 4, 'status' => TableStatus::AVAILABLE]);
        $existing->id = 2;
        $updated = new Table(['number' => 2, 'capacity' => 6, 'status' => TableStatus::RESERVED, 'label' => 'Terraza']);
        $updated->id = 2;
        $dto = new UpdateTableDto(2, 6, TableStatus::RESERVED, 'Terraza');

        /** @var TableRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(TableRepositoryInterface::class);
        $repository->expects($this->once())->method('findById')->with(2)->willReturn($existing);
        $repository->expects($this->once())->method('existsByNumber')->with(2, 2)->willReturn(false);
        $repository->expects($this->once())->method('update')->with($existing, $dto)->willReturn($updated);

        $service = new TableService($repository);

        $this->assertSame($updated, $service->update(2, $dto));
    }

    public function test_delete_removes_table(): void
    {
        $existing = new Table(['number' => 9, 'capacity' => 2, 'status' => TableStatus::AVAILABLE]);
        $existing->id = 9;

        /** @var TableRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(TableRepositoryInterface::class);
        $repository->expects($this->once())->method('findById')->with(9)->willReturn($existing);
        $repository->expects($this->once())->method('delete')->with($existing);

        $service = new TableService($repository);
        $service->delete(9);
    }
}
