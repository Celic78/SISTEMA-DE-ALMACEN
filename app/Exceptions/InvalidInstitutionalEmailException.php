<?php

declare(strict_types=1);

namespace App\Exceptions;

class InvalidInstitutionalEmailException extends BusinessRuleException
{
    public function __construct(string $message = 'Error, los datos ingresados no coinciden')
    {
        parent::__construct($message, 422);
    }
}
