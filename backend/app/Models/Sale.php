<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $tip_percent
 * @property int $subtotal_cents
 * @property int $tip_cents
 * @property int $total_cents
 * @property Carbon $paid_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order|null $order
 */
final class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    /** @var string */
    protected $table = 'sales';

    /** @var list<string> */
    protected $fillable = [
        'order_id',
        'tip_percent',
        'subtotal_cents',
        'tip_cents',
        'total_cents',
        'paid_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'int',
            'order_id' => 'int',
            'tip_percent' => 'int',
            'subtotal_cents' => 'int',
            'tip_cents' => 'int',
            'total_cents' => 'int',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
