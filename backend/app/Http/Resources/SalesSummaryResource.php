<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\Sale\SalesSummaryDto;
use Illuminate\Http\Request;

/**
 * @property-read SalesSummaryDto $resource
 */
final class SalesSummaryResource extends BaseJsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, int>
     */
    public function toArray($request): array
    {
        return [
            'count' => $this->resource->count(),
            'subtotal_cents' => $this->resource->subtotalCents(),
            'tip_cents' => $this->resource->tipCents(),
            'total_cents' => $this->resource->totalCents(),
        ];
    }
}
