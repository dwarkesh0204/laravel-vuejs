<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientBalanceException extends RuntimeException
{
    protected string $required;
    protected string $available;
    protected string $currency;

    public function __construct(string $required, string $available, string $currency = 'USD')
    {
        $this->required = $required;
        $this->available = $available;
        $this->currency = $currency;

        parent::__construct(
            sprintf(
                'Insufficient %s balance. Required: %s, Available: %s',
                $currency,
                $required,
                $available
            )
        );
    }

    public function getRequired(): string
    {
        return $this->required;
    }

    public function getAvailable(): string
    {
        return $this->available;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}

