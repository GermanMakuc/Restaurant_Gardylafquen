<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\HealthStatusDto;
use Illuminate\Database\ConnectionInterface;
use Throwable;

final class HealthService
{
    private ConnectionInterface $db;

    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
    }

    public function check(): HealthStatusDto
    {
        $databaseStatus = 'ok';

        try {
            $this->db->select('SELECT 1');
        } catch (Throwable $exception) {
            $databaseStatus = 'error';
        }

        return new HealthStatusDto(
            $databaseStatus === 'ok' ? 'ok' : 'degraded',
            'restaurant-gardylafquen-api',
            $databaseStatus,
            gmdate('Y-m-d\TH:i:s\Z')
        );
    }
}
