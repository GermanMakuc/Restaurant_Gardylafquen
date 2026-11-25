<?php

declare(strict_types=1);

namespace App\Exceptions;

final class DuplicateTableNumberException extends DomainException
{
    public function __construct(int $number)
    {
        parent::__construct(
            sprintf('Ya existe una mesa registrada con el número %d.', $number),
            'duplicate_table_number',
            409
        );
    }
}
