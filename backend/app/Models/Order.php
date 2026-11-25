<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $table_id
 * @property int $waiter_id
 * @property string $status
 * @property string|null $notes
 * @property int $subtotal_cents
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read Table|null $table
 * @property-read Waiter|null $waiter
 * @property-read Collection<int, OrderItem> $items
 */
final class Order extends Model
{
    use SoftDeletes;

    /** @var string */
    protected $table = 'restaurant_orders';

    /** @var array<int, string> */
    protected $fillable = [
        'table_id',
        'waiter_id',
        'status',
        'notes',
        'subtotal_cents',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'id' => 'int',
        'table_id' => 'int',
        'waiter_id' => 'int',
        'subtotal_cents' => 'int',
        'deleted_at' => 'datetime',
    ];

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(Waiter::class, 'waiter_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
