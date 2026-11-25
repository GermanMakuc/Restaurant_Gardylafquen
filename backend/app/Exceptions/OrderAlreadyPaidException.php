<?php

declare(strict_types=1);

namespace App\Exceptions;

final class OrderAlreadyPaidException extends DomainException
{
    public function __construct(int $orderId)
    {
        parent::__construct(
            sprintf('La orden #%d ya fue pagada o cerrada.', $orderId),
            'order_already_paid',
            409
        );
    }
}
