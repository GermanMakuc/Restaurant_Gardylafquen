<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class HealthStatusDto
{
    public function __construct(
        public string $status,
        public string $service,
        public string $database,
        public string $timestamp,
    ) {
    }
}
