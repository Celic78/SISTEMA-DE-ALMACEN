<?php

declare(strict_types=1);

namespace App\Exceptions;

class DuplicateSolicitudException extends BusinessRuleException
{
    public function __construct(string $message = 'Ya existe un reporte idéntico registrado previamente en proceso de atención.')
    {
        parent::__construct($message, 422);
    }
}
