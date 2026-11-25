<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

final class LoginDto
{
    private string $email;

    private string $password;

    public function __construct(string $email, string $password)
    {
        $this->email = mb_strtolower(trim($email));
        $this->password = $password;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $email = isset($data['email']) && is_string($data['email']) ? $data['email'] : '';
        $password = isset($data['password']) && is_string($data['password']) ? $data['password'] : '';

        return new self($email, $password);
    }

    public function email(): string
    {
        return $this->email;
    }

    public function password(): string
    {
        return $this->password;
    }
}
