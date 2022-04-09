<?php

namespace App\Exceptions;

use Exception;
use JetBrains\PhpStorm\Pure;
use Throwable;

class InvalidTotpIntervalException extends TotpException
{
    private int $m_interval;

    #[Pure] public function __construct(int $interval, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->m_interval = $interval;
    }

    public function getInterval(): int
    {
        return $this->m_interval;
    }
}
