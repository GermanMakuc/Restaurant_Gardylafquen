<?php

declare(strict_types=1);

namespace App\Exceptions;

final class WaiterNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct(
            sprintf('El mesero #%d no existe.', $id),
            'waiter_not_found',
            404
        );
    }
}
