<?php

declare(strict_types=1);

namespace App\DTOs\Table;

use App\Enums\TableStatus;

final class UpdateTableDto
{
    private int $number;

    private int $capacity;

    private string $status;

    private ?string $label;

    public function __construct(
        int $number,
        int $capacity = 4,
        ?string $status = null,
        ?string $label = null
    ) {
        $trimmedLabel = $label !== null ? trim($label) : null;
        $this->number = $number;
        $this->capacity = $capacity;
        $this->status = $status ?? TableStatus::AVAILABLE->value;
        $this->label = ($trimmedLabel !== null && $trimmedLabel !== '') ? $trimmedLabel : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $number = isset($data['number']) && is_numeric($data['number']) ? (int) $data['number'] : 0;
        $capacity = isset($data['capacity']) && is_numeric($data['capacity']) ? (int) $data['capacity'] : 4;
        $status = isset($data['status']) && is_string($data['status']) ? $data['status'] : TableStatus::AVAILABLE->value;
        $label = isset($data['label']) && is_string($data['label']) ? $data['label'] : null;

        return new self($number, $capacity, $status, $label);
    }

    public function number(): int
    {
        return $this->number;
    }

    public function capacity(): int
    {
        return $this->capacity;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function label(): ?string
    {
        return $this->label;
    }
}
