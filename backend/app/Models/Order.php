<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $table_id
 * @property int $waiter_id
 * @property string $status
 * @property string|null $notes
 * @property int $subtotal_cents
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Table|null $table
 * @property-read Waiter|null $waiter
 * @property-read Collection<int, OrderItem> $items
 */
final class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;
    use SoftDeletes;

    /** @var string */
    protected $table = 'restaurant_orders';

    /** @var list<string> */
    protected $fillable = [
        'table_id',
        'waiter_id',
        'status',
        'notes',
        'subtotal_cents',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'int',
            'table_id' => 'int',
            'waiter_id' => 'int',
            'subtotal_cents' => 'int',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Table, $this>
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    /**
     * @return BelongsTo<Waiter, $this>
     */
    public function waiter(): BelongsTo
    {
        return $this->belongsTo(Waiter::class, 'waiter_id');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
