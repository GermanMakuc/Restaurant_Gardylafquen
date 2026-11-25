<?php

declare(strict_types=1);

namespace App\Exceptions;

final class ProductNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct(
            sprintf('El producto #%d no existe.', $id),
            'product_not_found',
            404
        );
    }
}
