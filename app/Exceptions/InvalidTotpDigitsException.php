<?php

namespace App\Exceptions;

use JetBrains\PhpStorm\Pure;
use Throwable;

class InvalidTotpDigitsException extends TotpException
{
    private int $m_digits;

    #[Pure] public function __construct(int $digits, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->m_digits = $digits;
    }

    public function getDigits(): int
    {
        return $this->m_digits;
    }
}
