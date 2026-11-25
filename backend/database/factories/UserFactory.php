<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

/** @var Factory $factory */
$factory->define(User::class, static function (Faker $faker): array {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'role' => UserRole::WAITER,
        'remember_token' => Str::random(10),
        'api_token' => null,
    ];
});
