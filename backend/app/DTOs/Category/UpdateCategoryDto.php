<?php

declare(strict_types=1);

namespace App\DTOs\Category;

final class UpdateCategoryDto
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = trim($name);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $name = isset($data['name']) && is_string($data['name']) ? $data['name'] : '';

        return new self($name);
    }

    public function name(): string
    {
        return $this->name;
    }
}
