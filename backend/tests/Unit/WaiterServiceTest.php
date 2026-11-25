<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTOs\Waiter\CreateWaiterDto;
use App\DTOs\Waiter\ListWaitersDto;
use App\DTOs\Waiter\UpdateWaiterDto;
use App\Exceptions\DuplicateWaiterEmailException;
use App\Exceptions\WaiterNotFoundException;
use App\Models\Waiter;
use App\Repositories\Contracts\WaiterRepositoryInterface;
use App\Services\WaiterService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class WaiterServiceTest extends TestCase
{
    public function test_list_delegates_to_repository(): void
    {
        $dto = new ListWaitersDto('Camila', true, 'name', 10);
        /** @var LengthAwarePaginator<int, Waiter>&MockObject $paginator */
        $paginator = $this->createMock(LengthAwarePaginator::class);

        /** @var WaiterRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(WaiterRepositoryInterface::class);
        $repository->expects($this->once())->method('paginate')->with($dto)->willReturn($paginator);

        $service = new WaiterService($repository);

        $this->assertSame($paginator, $service->list($dto));
    }

    public function test_find_or_fail_throws_when_missing(): void
    {
        /** @var WaiterRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(WaiterRepositoryInterface::class);
        $repository->expects($this->once())->method('findById')->with(77)->willReturn(null);

        $service = new WaiterService($repository);

        $this->expectException(WaiterNotFoundException::class);
        $service->findOrFail(77);
    }

    public function test_create_persists_waiter_when_email_is_unique(): void
    {
        $dto = new CreateWaiterDto('Camila Rojas', 'camila@example.test', true);
        $created = new Waiter(['name' => 'Camila Rojas', 'email' => 'camila@example.test', 'is_active' => true]);
        $created->id = 1;

        /** @var WaiterRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(WaiterRepositoryInterface::class);
        $repository->expects($this->once())->method('existsByEmail')->with('camila@example.test', null)->willReturn(false);
        $repository->expects($this->once())->method('create')->with($dto)->willReturn($created);

        $service = new WaiterService($repository);

        $this->assertSame($created, $service->create($dto));
    }

    public function test_create_throws_when_email_already_exists(): void
    {
        $dto = new CreateWaiterDto('Camila', 'camila@example.test', true);

        /** @var WaiterRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(WaiterRepositoryInterface::class);
        $repository->expects($this->once())->method('existsByEmail')->with('camila@example.test', null)->willReturn(true);
        $repository->expects($this->never())->method('create');

        $service = new WaiterService($repository);

        $this->expectException(DuplicateWaiterEmailException::class);
        $service->create($dto);
    }

    public function test_update_modifies_waiter_when_found_and_email_unique(): void
    {
        $existing = new Waiter(['name' => 'Camila', 'email' => 'camila@example.test', 'is_active' => true]);
        $existing->id = 3;
        $updated = new Waiter(['name' => 'Camila R.', 'email' => 'camila.r@example.test', 'is_active' => false]);
        $updated->id = 3;
        $dto = new UpdateWaiterDto('Camila R.', 'camila.r@example.test', false);

        /** @var WaiterRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(WaiterRepositoryInterface::class);
        $repository->expects($this->once())->method('findById')->with(3)->willReturn($existing);
        $repository->expects($this->once())->method('existsByEmail')->with('camila.r@example.test', 3)->willReturn(false);
        $repository->expects($this->once())->method('update')->with($existing, $dto)->willReturn($updated);

        $service = new WaiterService($repository);

        $this->assertSame($updated, $service->update(3, $dto));
    }

    public function test_delete_removes_waiter(): void
    {
        $existing = new Waiter(['name' => 'Mateo', 'email' => 'mateo@example.test', 'is_active' => true]);
        $existing->id = 4;

        /** @var WaiterRepositoryInterface&MockObject $repository */
        $repository = $this->createMock(WaiterRepositoryInterface::class);
        $repository->expects($this->once())->method('findById')->with(4)->willReturn($existing);
        $repository->expects($this->once())->method('delete')->with($existing);

        $service = new WaiterService($repository);
        $service->delete(4);
    }
}
