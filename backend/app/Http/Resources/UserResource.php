<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read User $resource
 */
final class UserResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, int|string|null>
     */
    public function toArray($request): array
    {
        $createdAt = $this->resource->created_at;

        return [
            'id' => (int) $this->resource->id,
            'name' => (string) $this->resource->name,
            'email' => (string) $this->resource->email,
            'role' => (string) $this->resource->role,
            'created_at' => $createdAt !== null ? $createdAt->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') : null,
        ];
    }
}
