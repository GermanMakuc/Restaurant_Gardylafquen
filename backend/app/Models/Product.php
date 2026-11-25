<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $category_id
 * @property int $price
 * @property int $price_cents
 * @property string $path
 * @property string|null $image_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Category|null $category
 */
final class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;
    use SoftDeletes;

    /** @var string */
    protected $table = 'products';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'price',
        'price_cents',
        'path',
        'image_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'int',
            'category_id' => 'int',
            'price' => 'int',
            'price_cents' => 'int',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
