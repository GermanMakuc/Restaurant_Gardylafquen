<?php

declare(strict_types=1);

namespace App\Exceptions;

final class TableNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct(
            sprintf('La mesa #%d no existe.', $id),
            'table_not_found',
            404
        );
    }
}
