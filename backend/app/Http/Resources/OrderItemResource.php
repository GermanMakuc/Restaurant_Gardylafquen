<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\OrderItem;
use Illuminate\Http\Request;

/**
 * @property-read OrderItem $resource
 */
final class OrderItemResource extends BaseJsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, int|string>
     */
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->resource->id,
            'product_id' => (int) $this->resource->product_id,
            'product_name' => (string) $this->resource->product_name,
            'unit_price_cents' => (int) $this->resource->unit_price_cents,
            'quantity' => (int) $this->resource->quantity,
            'line_total_cents' => (int) $this->resource->line_total_cents,
        ];
    }
}
