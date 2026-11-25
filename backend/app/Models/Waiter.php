<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property bool $is_active
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
final class Waiter extends Model
{
    use SoftDeletes;

    /** @var string */
    protected $table = 'waiters';

    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'email',
        'is_active',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'id' => 'int',
        'is_active' => 'bool',
        'deleted_at' => 'datetime',
    ];
}
