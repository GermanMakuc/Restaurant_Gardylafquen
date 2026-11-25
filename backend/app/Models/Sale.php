<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int $tip_percent
 * @property int $subtotal_cents
 * @property int $tip_cents
 * @property int $total_cents
 * @property \Carbon\Carbon $paid_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read Order|null $order
 */
final class Sale extends Model
{
    /** @var string */
    protected $table = 'sales';

    /** @var array<int, string> */
    protected $fillable = [
        'order_id',
        'tip_percent',
        'subtotal_cents',
        'tip_cents',
        'total_cents',
        'paid_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'id' => 'int',
        'order_id' => 'int',
        'tip_percent' => 'int',
        'subtotal_cents' => 'int',
        'tip_cents' => 'int',
        'total_cents' => 'int',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
