<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;

/**
 * @property-read Category $resource
 */
final class CategoryResource extends BaseJsonResource
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
            'name' => (string) $this->resource->name,
            'created_at' => $createdAt !== null ? $createdAt->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') : null,
            'updated_at' => $updatedAt !== null ? $updatedAt->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') : null,
        ];
    }
}
