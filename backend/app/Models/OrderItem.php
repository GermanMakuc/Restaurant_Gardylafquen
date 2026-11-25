<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property string $product_name
 * @property int $unit_price_cents
 * @property int $quantity
 * @property int $line_total_cents
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read Order|null $order
 * @property-read Product|null $product
 */
final class OrderItem extends Model
{
    /** @var string */
    protected $table = 'order_items';

    /** @var array<int, string> */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'unit_price_cents',
        'quantity',
        'line_total_cents',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'id' => 'int',
        'order_id' => 'int',
        'product_id' => 'int',
        'unit_price_cents' => 'int',
        'quantity' => 'int',
        'line_total_cents' => 'int',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
