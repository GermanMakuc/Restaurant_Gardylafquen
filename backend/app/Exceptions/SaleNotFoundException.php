<?php

declare(strict_types=1);

namespace App\Exceptions;

final class SaleNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct(
            sprintf('La venta #%d no existe.', $id),
            'sale_not_found',
            404
        );
    }
}
