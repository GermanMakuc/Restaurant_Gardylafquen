<?php

declare(strict_types=1);

namespace App\Exceptions;

final class TableAlreadyOccupiedException extends DomainException
{
    public function __construct(int $tableNumber)
    {
        parent::__construct(
            sprintf('La mesa #%d ya tiene una orden activa o no está disponible.', $tableNumber),
            'table_already_occupied',
            409
        );
    }
}
