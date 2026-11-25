<?php

declare(strict_types=1);

namespace App\DTOs\Waiter;

final readonly class UpdateWaiterDto
{
    public function __construct(
        public string $name,
        public string $email,
        public bool $isActive = true,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $name = isset($data['name']) && is_string($data['name']) ? trim($data['name']) : '';
        $email = isset($data['email']) && is_string($data['email']) ? mb_strtolower(trim($data['email'])) : '';
        $isActive = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        return new self($name, $email, $isActive);
    }
}
