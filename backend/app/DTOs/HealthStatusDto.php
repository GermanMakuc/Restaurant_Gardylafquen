<?php

declare(strict_types=1);

namespace App\DTOs;

final class HealthStatusDto
{
    private string $status;

    private string $service;

    private string $database;

    private string $timestamp;

    public function __construct(
        string $status,
        string $service,
        string $database,
        string $timestamp
    ) {
        $this->status = $status;
        $this->service = $service;
        $this->database = $database;
        $this->timestamp = $timestamp;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function service(): string
    {
        return $this->service;
    }

    public function database(): string
    {
        return $this->database;
    }

    public function timestamp(): string
    {
        return $this->timestamp;
    }
}
