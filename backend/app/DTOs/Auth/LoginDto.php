<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

final readonly class LoginDto
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $email = isset($data['email']) && is_string($data['email']) ? mb_strtolower(trim($data['email'])) : '';
        $password = isset($data['password']) && is_string($data['password']) ? $data['password'] : '';

        return new self($email, $password);
    }
}
