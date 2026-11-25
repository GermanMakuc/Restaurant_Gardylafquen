<?php

declare(strict_types=1);

namespace App\Enums;

enum TableStatus: string
{
    case AVAILABLE = 'available';
    case OCCUPIED = 'occupied';
    case RESERVED = 'reserved';
    case INACTIVE = 'inactive';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
