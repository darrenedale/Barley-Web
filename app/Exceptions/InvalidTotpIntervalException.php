<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class InvalidTotpIntervalException extends Exception
{
    private int $m_interval;

    public function __construct(int $interval, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->m_interval = $interval;
    }

    public function getInterval(): int
    {
        return $this->m_interval;
    }
}
