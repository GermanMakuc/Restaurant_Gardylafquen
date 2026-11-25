<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string|null $api_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
final class User extends Authenticatable
{
    use Notifiable;

    /** @var string */
    protected $table = 'users';

    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /** @var array<int, string> */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'id' => 'int',
    ];

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }
}
