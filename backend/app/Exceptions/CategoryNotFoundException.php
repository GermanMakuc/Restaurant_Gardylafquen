<?php

declare(strict_types=1);

namespace App\Exceptions;

final class CategoryNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct(
            sprintf('La categoría #%d no existe.', $id),
            'category_not_found',
            404
        );
    }
}
