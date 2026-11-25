<?php

declare(strict_types=1);

namespace App\Http\Resources;

use ArrayAccess;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Compatibility base resource for Laravel 5.7 running on PHP 7.4:
 * avoids DelegatesToResource::offsetExists() calling array_key_exists() on objects.
 */
abstract class BaseJsonResource extends JsonResource
{
    /**
     * @param  mixed  $offset
     */
    public function offsetExists($offset): bool
    {
        if (is_array($this->resource)) {
            return array_key_exists($offset, $this->resource);
        }

        if ($this->resource instanceof ArrayAccess) {
            return $this->resource->offsetExists($offset);
        }

        if (is_object($this->resource)) {
            return property_exists($this->resource, (string) $offset);
        }

        return false;
    }
}
