<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Administrador',
                'email' => 'admin@gardylafquen.test',
                'password' => 'Admin123!',
                'role' => UserRole::ADMIN->value,
            ],
            [
                'name' => 'Cajero',
                'email' => 'caja@gardylafquen.test',
                'password' => 'Caja1234!',
                'role' => UserRole::CASHIER->value,
            ],
            [
                'name' => 'Mesero Demo',
                'email' => 'mesero@gardylafquen.test',
                'password' => 'Mesero123!',
                'role' => UserRole::WAITER->value,
            ],
        ];

        foreach ($accounts as $account) {
            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                    'role' => $account['role'],
                ]
            );
        }
    }
}
