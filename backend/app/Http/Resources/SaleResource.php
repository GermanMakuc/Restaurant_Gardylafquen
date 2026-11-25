<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Sale;
use Illuminate\Http\Request;

/**
 * @property-read Sale $resource
 */
final class SaleResource extends BaseJsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $paidAt = $this->resource->paid_at;
        $createdAt = $this->resource->created_at;

        return [
            'id' => (int) $this->resource->id,
            'order_id' => (int) $this->resource->order_id,
            'order' => OrderResource::make($this->whenLoaded('order')),
            'tip_percent' => (int) $this->resource->tip_percent,
            'subtotal_cents' => (int) $this->resource->subtotal_cents,
            'tip_cents' => (int) $this->resource->tip_cents,
            'total_cents' => (int) $this->resource->total_cents,
            'paid_at' => $paidAt->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z'),
            'created_at' => $createdAt !== null ? $createdAt->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') : null,
        ];
    }
}
