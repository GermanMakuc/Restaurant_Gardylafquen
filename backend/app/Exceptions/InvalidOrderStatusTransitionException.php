<?php

declare(strict_types=1);

namespace App\Exceptions;

final class InvalidOrderStatusTransitionException extends DomainException
{
    public function __construct(string $from, string $to)
    {
        parent::__construct(
            sprintf('No es válido cambiar una orden de "%s" a "%s".', $from, $to),
            'invalid_order_status_transition',
            409
        );
    }
}
