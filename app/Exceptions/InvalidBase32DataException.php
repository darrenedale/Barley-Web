<?php

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Exception thrown when data that is expected to be base32 encoded is not valid.
 */
class InvalidBase32DataException extends Exception
{
    private string $m_data;

    /**
     * Initialise a new InvalidBase32DataException.
     *
     * @param string $data The invalid base32 data.
     * @param string $message An optional message stating what's wrong. Default is an empty string.
     * @param int $code An optional error code. Default is 0.
     * @param \Throwable|null $previous The previous exception, if any. Default is null.
     */
    public function __construct(string $data, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->m_data = $data;
    }

    /**
     * Fetch the data that is not valid base32.
     *
     * @return string The data.
     */
    public function getData(): string
    {
        return $this->m_data;
    }
}
