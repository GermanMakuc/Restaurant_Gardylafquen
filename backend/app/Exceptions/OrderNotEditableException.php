<?php

declare(strict_types=1);

namespace App\Exceptions;

final class OrderNotEditableException extends DomainException
{
    public function __construct(int $orderId, string $status)
    {
        parent::__construct(
            sprintf('La orden #%d está en estado "%s" y no puede modificarse.', $orderId, $status),
            'order_not_editable',
            409
        );
    }
}
