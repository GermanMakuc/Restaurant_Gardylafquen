<?php

declare(strict_types=1);

namespace App\DTOs\Product;

final class UpdateProductDto
{
    private string $name;

    private int $categoryId;

    private int $priceCents;

    private ?string $description;

    public function __construct(
        string $name,
        int $categoryId,
        int $priceCents,
        ?string $description = null
    ) {
        $trimmedDescription = $description !== null ? trim($description) : null;
        $this->name = trim($name);
        $this->categoryId = $categoryId;
        $this->priceCents = $priceCents;
        $this->description = ($trimmedDescription !== null && $trimmedDescription !== '') ? $trimmedDescription : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $name = isset($data['name']) && is_string($data['name']) ? $data['name'] : '';
        $categoryId = isset($data['category_id']) && is_numeric($data['category_id']) ? (int) $data['category_id'] : 0;
        $priceCents = isset($data['price_cents']) && is_numeric($data['price_cents']) ? (int) $data['price_cents'] : 0;
        $description = isset($data['description']) && is_string($data['description']) ? $data['description'] : null;

        return new self($name, $categoryId, $priceCents, $description);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function categoryId(): int
    {
        return $this->categoryId;
    }

    public function priceCents(): int
    {
        return $this->priceCents;
    }

    public function description(): ?string
    {
        return $this->description;
    }
}
