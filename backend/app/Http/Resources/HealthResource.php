<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\HealthStatusDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read HealthStatusDto $resource
 */
final class HealthResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, string>
     */
    public function toArray($request): array
    {
        return [
            'status' => $this->resource->status,
            'service' => $this->resource->service,
            'database' => $this->resource->database,
            'timestamp' => $this->resource->timestamp,
        ];
    }
}
