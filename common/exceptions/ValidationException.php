<?php

namespace common\exceptions;

use RuntimeException;
use Throwable;

class ValidationException extends RuntimeException
{
    private array $validationErrors;

    public function __construct(array $validationErrors, Throwable|null $previous = null)
    {
        $this->validationErrors = $validationErrors;

        parent::__construct('', 0, $previous);
    }

    public function getErrors(): array
    {
        return $this->validationErrors;
    }
}
