<?php

namespace App\Exceptions;

use JetBrains\PhpStorm\Pure;
use Throwable;

/**
 * Exception thrown when an attempt has been made to set some kind of dimension to an invalid value.
 */
class InvalidDimensionException extends \Exception
{
    /**
     * @var string The name of the dimension, if provided.
     */
    private string $m_dimensionName;

    /**
     * @var int|float The invalid dimension value.
     */
    private int|float $m_value;

    /**
     * Intitialise a new InvalidDimensionException.
     *
     * @param int|float $value The value that is not valid for the dimension.
     * @param string $dimensionName The name of the dimension, if this will aid troubleshooting. Defaults to an empty string.
     * @param string $message The message describing the problem. Defaults to an empty string.
     * @param int $code The error code for the problem, if there is one. Defaults to 0.
     * @param Throwable|null $previous The previous exception, if there is one. Defaults to null.
     */
    #[Pure] public function __construct(int|float $value, string $dimensionName = "", string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->m_value         = $value;
        $this->m_dimensionName = $dimensionName;
    }

    /**
     * Fetch the name of the dimension that was found to be invalid.
     *
     * In cases where there are multiple dimensions (e.g. both width and height), this should disambiguate to aid
     * troubleshooting. If it's obvious from other context which dimension is the problem, this can be an empty string.
     *
     * @return string The name of the dimension that was invalid.
     */
    public function getDimensionName(): string
    {
        return $this->m_dimensionName;
    }

    /**
     * Fetch the invalid value for the dimension.
     *
     * @return int|float The invalid value for the dimension.
     */
    public function getDimensionValue(): int | float
    {
        return $this->m_value;
    }
}
