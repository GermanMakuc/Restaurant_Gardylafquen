<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TableFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $number
 * @property string|null $label
 * @property int $capacity
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
final class Table extends Model
{
    /** @use HasFactory<TableFactory> */
    use HasFactory;
    use SoftDeletes;

    /** @var string */
    protected $table = 'tables';

    /** @var list<string> */
    protected $fillable = [
        'number',
        'label',
        'capacity',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'int',
            'number' => 'int',
            'capacity' => 'int',
            'deleted_at' => 'datetime',
        ];
    }
}
