<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Table;
use Illuminate\Http\Request;

/**
 * @property-read Table $resource
 */
final class TableResource extends BaseJsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, int|string|null>
     */
    public function toArray($request): array
    {
        $createdAt = $this->resource->created_at;
        $updatedAt = $this->resource->updated_at;

        return [
            'id' => (int) $this->resource->id,
            'number' => (int) $this->resource->number,
            'label' => $this->resource->label,
            'capacity' => (int) $this->resource->capacity,
            'status' => (string) $this->resource->status,
            'created_at' => $createdAt !== null ? $createdAt->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') : null,
            'updated_at' => $updatedAt !== null ? $updatedAt->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') : null,
        ];
    }
}
