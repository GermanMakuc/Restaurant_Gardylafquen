<?php

declare(strict_types=1);

namespace App\DTOs\Product;

final readonly class CreateProductDto
{
    public function __construct(
        public string $name,
        public int $categoryId,
        public int $priceCents,
        public ?string $description = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $name = isset($data['name']) && is_string($data['name']) ? trim($data['name']) : '';
        $categoryId = isset($data['category_id']) && is_numeric($data['category_id']) ? (int) $data['category_id'] : 0;
        $priceCents = isset($data['price_cents']) && is_numeric($data['price_cents']) ? (int) $data['price_cents'] : 0;
        $rawDescription = isset($data['description']) && is_string($data['description']) ? trim($data['description']) : null;
        $description = ($rawDescription !== null && $rawDescription !== '') ? $rawDescription : null;

        return new self($name, $categoryId, $priceCents, $description);
    }
}
