<?php

declare(strict_types=1);

namespace App\Exceptions;

final class DuplicateWaiterEmailException extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(
            sprintf('Ya existe un mesero registrado con el correo "%s".', $email),
            'duplicate_waiter_email',
            409
        );
    }
}
