<?php

namespace App\Application\DealerApplication\Exceptions;

class DealerApplicationValidationException extends DealerApplicationDomainException
{
    public function __construct(
        string $message,
        private array $errors = []
    ) {
        parent::__construct($message);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}

