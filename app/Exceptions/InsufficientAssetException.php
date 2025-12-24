<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientAssetException extends RuntimeException
{
    protected string $required;
    protected string $available;
    protected string $symbol;

    public function __construct(string $required, string $available, string $symbol)
    {
        $this->required = $required;
        $this->available = $available;
        $this->symbol = $symbol;

        parent::__construct(
            sprintf(
                'Insufficient %s balance. Required: %s, Available: %s',
                $symbol,
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

    public function getSymbol(): string
    {
        return $this->symbol;
    }
}

