<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
final class Category extends Model
{
    /** @var string */
    protected $table = 'categories';

    /** @var array<int, string> */
    protected $fillable = [
        'name',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'id' => 'int',
    ];
}
