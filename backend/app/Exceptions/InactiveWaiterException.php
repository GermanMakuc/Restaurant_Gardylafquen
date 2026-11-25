<?php

declare(strict_types=1);

namespace App\Exceptions;

final class InactiveWaiterException extends DomainException
{
    public function __construct(int $waiterId)
    {
        parent::__construct(
            sprintf('El mesero #%d está inactivo y no puede tomar órdenes.', $waiterId),
            'inactive_waiter',
            409
        );
    }
}
