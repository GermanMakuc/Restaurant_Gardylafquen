<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WaiterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class Waiter extends Model
{
    /** @use HasFactory<WaiterFactory> */
    use HasFactory;
    use SoftDeletes;

    /** @var string */
    protected $table = 'waiters';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'int',
            'is_active' => 'bool',
            'deleted_at' => 'datetime',
        ];
    }
}
