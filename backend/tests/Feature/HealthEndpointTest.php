<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Database\ConnectionInterface;
use RuntimeException;
use Tests\TestCase;

final class HealthEndpointTest extends TestCase
{
    public function test_health_endpoint_returns_ok_payload(): void
    {
        $db = $this->createMock(ConnectionInterface::class);
        $db->expects($this->once())
            ->method('select')
            ->with('SELECT 1')
            ->willReturn([]);

        $this->app->instance(ConnectionInterface::class, $db);

        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['status', 'service', 'database', 'timestamp'],
            ])
            ->assertJson([
                'data' => [
                    'status' => 'ok',
                    'service' => 'restaurant-gardylafquen-api',
                    'database' => 'ok',
                ],
            ]);
    }

    public function test_health_endpoint_returns_503_when_database_is_unavailable(): void
    {
        $db = $this->createMock(ConnectionInterface::class);
        $db->expects($this->once())
            ->method('select')
            ->with('SELECT 1')
            ->willThrowException(new RuntimeException('DB down'));

        $this->app->instance(ConnectionInterface::class, $db);

        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(503)
            ->assertJson([
                'data' => [
                    'status' => 'degraded',
                    'database' => 'error',
                ],
            ]);
    }

    public function test_unknown_api_route_returns_uniform_404_json(): void
    {
        $response = $this->getJson('/api/v1/does-not-exist');

        $response->assertStatus(404)
            ->assertJsonStructure(['message', 'code'])
            ->assertJson(['code' => 'not_found']);
    }
}
