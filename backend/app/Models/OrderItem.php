<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property string $product_name
 * @property int $unit_price_cents
 * @property int $quantity
 * @property int $line_total_cents
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order|null $order
 * @property-read Product|null $product
 */
final class OrderItem extends Model
{
    /** @var string */
    protected $table = 'order_items';

    /** @var list<string> */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'unit_price_cents',
        'quantity',
        'line_total_cents',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'int',
            'order_id' => 'int',
            'product_id' => 'int',
            'unit_price_cents' => 'int',
            'quantity' => 'int',
            'line_total_cents' => 'int',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
