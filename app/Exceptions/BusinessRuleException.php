<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class BusinessRuleException extends RuntimeException
{
    /**
     * @param  string  $message
     * @param  int  $statusCode
     */
    public function __construct(string $message, public int $statusCode = 422)
    {
        parent::__construct($message, $statusCode);
    }
}
