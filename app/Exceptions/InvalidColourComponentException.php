<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class InvalidColourComponentException extends Exception
{
    /**
     * @var int The invalid value.
     */
    private int $m_value;

    /**
     * @var string The component that the invalid value is intended to represent.
     */
    private string $m_component;

    /**
     * Initialise a new InvalidColourComponentException.
     *
     * @param int $value The invalid value.
     * @param string $component The name of the component the value was intended to represent.
     * @param string $message The optional error message. Defaults to an empty string.
     * @param int $code The optional error code. Defaults to 0.
     * @param \Throwable|null $previous The optional previous Throwable that occurred before this. Defaults to null.
     */
    public function __construct(int $value, string $component, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->m_value = $value;
        $this->m_component = $component;
    }

    /**
     * Fetch the invalid value.
     *
     * @return int The value.
     */
    public function getValue(): int
    {
        return $this->m_value;
    }

    /**
     * Fetch the component that the invalid value is intended to represent.
     *
     * @return string The component.
     */
    public function getComponent(): string
    {
        return $this->m_component;
    }
}
