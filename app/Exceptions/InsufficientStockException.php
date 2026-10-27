<?php

declare(strict_types=1);

namespace App\Exceptions;

class InsufficientStockException extends BusinessRuleException
{
    public function __construct(string $message = 'Existencias insuficientes en almacén para despachar el material solicitado.')
    {
        parent::__construct($message, 422);
    }
}
