<?php

declare(strict_types=1);

namespace App\Enums;

final class UserRole
{
    public const ADMIN = 'admin';
    public const CASHIER = 'cashier';
    public const WAITER = 'waiter';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            self::ADMIN,
            self::CASHIER,
            self::WAITER,
        ];
    }
}
