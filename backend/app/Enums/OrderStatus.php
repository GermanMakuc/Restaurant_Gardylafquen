<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case WAITING = 'waiting';
    case SERVED = 'served';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Statuses that hold a table open.
     *
     * @return array<int, string>
     */
    public static function activeValues(): array
    {
        return [
            self::WAITING->value,
            self::SERVED->value,
        ];
    }
}
