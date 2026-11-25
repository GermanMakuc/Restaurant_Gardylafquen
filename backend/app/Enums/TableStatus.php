<?php

declare(strict_types=1);

namespace App\Enums;

final class TableStatus
{
    public const AVAILABLE = 'available';
    public const OCCUPIED = 'occupied';
    public const RESERVED = 'reserved';
    public const INACTIVE = 'inactive';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            self::AVAILABLE,
            self::OCCUPIED,
            self::RESERVED,
            self::INACTIVE,
        ];
    }
}
