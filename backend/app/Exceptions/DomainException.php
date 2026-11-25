<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

abstract class DomainException extends RuntimeException
{
    /** @var string */
    private string $errorCode;

    /** @var int */
    private int $statusCode;

    public function __construct(
        string $message,
        string $errorCode = 'domain_error',
        int $statusCode = 409
    ) {
        parent::__construct($message);
        $this->errorCode = $errorCode;
        $this->statusCode = $statusCode;
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
