<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $category_id
 * @property int $price
 * @property int $price_cents
 * @property string $path
 * @property string|null $image_path
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read Category|null $category
 */
final class Product extends Model
{
    use SoftDeletes;

    /** @var string */
    protected $table = 'products';

    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'price',
        'price_cents',
        'path',
        'image_path',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'id' => 'int',
        'category_id' => 'int',
        'price' => 'int',
        'price_cents' => 'int',
        'deleted_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
