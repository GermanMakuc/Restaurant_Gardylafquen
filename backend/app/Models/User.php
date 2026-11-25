<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class User extends Authenticatable
{
    use HasApiTokens;
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;

    /** @var string */
    protected $table = 'users';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'int',
        ];
    }

    public function hasRole(UserRole ...$roles): bool
    {
        $roleValues = array_map(static fn (UserRole $role): string => $role->value, $roles);

        return in_array($this->role, $roleValues, true);
    }
}
