<?php

declare(strict_types=1);

namespace App\DTOs\Category;

final readonly class UpdateCategoryDto
{
    public function __construct(
        public string $name,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $name = isset($data['name']) && is_string($data['name']) ? trim($data['name']) : '';

        return new self($name);
    }
}
