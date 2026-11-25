<?php

declare(strict_types=1);

namespace App\DTOs\Table;

use App\Enums\TableStatus;

final readonly class UpdateTableDto
{
    public function __construct(
        public int $number,
        public int $capacity = 4,
        public string $status = TableStatus::AVAILABLE->value,
        public ?string $label = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $number = isset($data['number']) && is_numeric($data['number']) ? (int) $data['number'] : 0;
        $capacity = isset($data['capacity']) && is_numeric($data['capacity']) ? (int) $data['capacity'] : 4;
        $status = isset($data['status']) && is_string($data['status']) ? $data['status'] : TableStatus::AVAILABLE->value;
        $rawLabel = isset($data['label']) && is_string($data['label']) ? trim($data['label']) : null;
        $label = ($rawLabel !== null && $rawLabel !== '') ? $rawLabel : null;

        return new self($number, $capacity, $status, $label);
    }
}
