<?php

declare(strict_types=1);

namespace App\DTOs\Waiter;

final class UpdateWaiterDto
{
    private string $name;

    private string $email;

    private bool $isActive;

    public function __construct(string $name, string $email, bool $isActive = true)
    {
        $this->name = trim($name);
        $this->email = mb_strtolower(trim($email));
        $this->isActive = $isActive;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $name = isset($data['name']) && is_string($data['name']) ? $data['name'] : '';
        $email = isset($data['email']) && is_string($data['email']) ? $data['email'] : '';
        $isActive = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true;

        return new self($name, $email, $isActive);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
