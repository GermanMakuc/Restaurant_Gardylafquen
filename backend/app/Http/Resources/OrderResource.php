<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * @property-read Order $resource
 */
final class OrderResource extends BaseJsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $createdAt = $this->resource->created_at;
        $updatedAt = $this->resource->updated_at;

        return [
            'id' => (int) $this->resource->id,
            'table_id' => (int) $this->resource->table_id,
            'table' => TableResource::make($this->whenLoaded('table')),
            'waiter_id' => (int) $this->resource->waiter_id,
            'waiter' => WaiterResource::make($this->whenLoaded('waiter')),
            'status' => (string) $this->resource->status,
            'notes' => $this->resource->notes,
            'subtotal_cents' => (int) $this->resource->subtotal_cents,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $createdAt !== null ? $createdAt->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') : null,
            'updated_at' => $updatedAt !== null ? $updatedAt->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') : null,
        ];
    }
}
