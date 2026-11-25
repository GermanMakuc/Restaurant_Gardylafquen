<?php

declare(strict_types=1);

namespace App\Enums;

final class OrderStatus
{
    public const WAITING = 'waiting';
    public const SERVED = 'served';
    public const PAID = 'paid';
    public const CANCELLED = 'cancelled';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            self::WAITING,
            self::SERVED,
            self::PAID,
            self::CANCELLED,
        ];
    }

    /**
     * Statuses that hold a table open.
     *
     * @return array<int, string>
     */
    public static function activeValues(): array
    {
        return [
            self::WAITING,
            self::SERVED,
        ];
    }
}
