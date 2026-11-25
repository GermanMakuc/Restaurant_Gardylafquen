<?php

declare(strict_types=1);

namespace App\Exceptions;

final class DuplicateCategoryException extends DomainException
{
    public function __construct(string $name)
    {
        parent::__construct(
            sprintf('Ya existe una categoría con el nombre "%s".', $name),
            'duplicate_category',
            409
        );
    }
}
