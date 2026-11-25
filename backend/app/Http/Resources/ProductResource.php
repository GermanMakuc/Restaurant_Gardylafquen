<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read Product $resource
 */
final class ProductResource extends BaseJsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $createdAt = $this->resource->created_at;
        $updatedAt = $this->resource->updated_at;
        $imagePath = $this->resource->image_path;
        $imageUrl = null;

        if ($imagePath !== null && $imagePath !== '') {
            $diskName = (string) config('filesystems.product_images_disk', 'public');
            $disk = Storage::disk($diskName);
            $imageUrl = $disk instanceof FilesystemAdapter
                ? $disk->url($imagePath)
                : url('/storage/' . ltrim($imagePath, '/'));
        }

        return [
            'id' => (int) $this->resource->id,
            'name' => (string) $this->resource->name,
            'description' => $this->resource->description,
            'category_id' => (int) $this->resource->category_id,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'price_cents' => (int) $this->resource->price_cents,
            'image_url' => $imageUrl,
            'created_at' => $createdAt !== null ? $createdAt->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') : null,
            'updated_at' => $updatedAt !== null ? $updatedAt->copy()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') : null,
        ];
    }
}
