<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\Auth\AuthTokenDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read AuthTokenDto $resource
 */
final class AuthTokenResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'token' => $this->resource->token,
            'token_type' => 'Bearer',
            'user' => UserResource::make($this->resource->user),
        ];
    }
}
