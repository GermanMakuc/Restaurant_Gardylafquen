<?php

declare(strict_types=1);

namespace App\Exceptions;

final class OrderNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct(
            sprintf('La orden #%d no existe.', $id),
            'order_not_found',
            404
        );
    }
}
