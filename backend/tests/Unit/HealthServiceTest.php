<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\HealthService;
use Illuminate\Database\ConnectionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class HealthServiceTest extends TestCase
{
    public function test_it_returns_ok_when_database_query_succeeds(): void
    {
        /** @var ConnectionInterface&MockObject $db */
        $db = $this->createMock(ConnectionInterface::class);
        $db->expects($this->once())
            ->method('select')
            ->with('SELECT 1')
            ->willReturn([]);

        $service = new HealthService($db);
        $status = $service->check();

        $this->assertSame('ok', $status->status);
        $this->assertSame('ok', $status->database);
        $this->assertSame('restaurant-gardylafquen-api', $status->service);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $status->timestamp);
    }

    public function test_it_returns_degraded_when_database_query_fails(): void
    {
        /** @var ConnectionInterface&MockObject $db */
        $db = $this->createMock(ConnectionInterface::class);
        $db->expects($this->once())
            ->method('select')
            ->with('SELECT 1')
            ->willThrowException(new RuntimeException('Connection refused'));

        $service = new HealthService($db);
        $status = $service->check();

        $this->assertSame('degraded', $status->status);
        $this->assertSame('error', $status->database);
    }
}
