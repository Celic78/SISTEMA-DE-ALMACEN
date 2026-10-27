<?php

declare(strict_types=1);

namespace App\Exceptions;

class ApprovalRequiredException extends BusinessRuleException
{
    public function __construct(string $message = 'La orden de trabajo requiere visto bueno formal de Coordinación antes de proceder.')
    {
        parent::__construct($message, 403);
    }
}
