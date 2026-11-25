<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $number
 * @property string|null $label
 * @property int $capacity
 * @property string $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
final class Table extends Model
{
    use SoftDeletes;

    /** @var string */
    protected $table = 'tables';

    /** @var array<int, string> */
    protected $fillable = [
        'number',
        'label',
        'capacity',
        'status',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'id' => 'int',
        'number' => 'int',
        'capacity' => 'int',
        'deleted_at' => 'datetime',
    ];
}
