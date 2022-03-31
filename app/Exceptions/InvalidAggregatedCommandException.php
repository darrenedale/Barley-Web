<?php

namespace App\Exceptions;

use Stringable;
use Throwable;

/**
 * Exception thrown when an AggregateCommand is given an invalid command to run.
 */
class InvalidAggregatedCommandException extends \Exception
{
    public mixed $command;

    /**
     * Initialise a new exception instance.
     *
     * @param string $message The message indicating the problem.
     * @param mixed|null $command The command that was found to be invalid.
     * @param int $code The exception code.
     * @param \Throwable|null $previous The previously-thrown exception, if any.
     */
    public function __construct(string $message, mixed $command = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->command = $command;
    }

    /**
     * Get a stringified representation of the invalid command.
     *
     * @return string
     */
    public function getCommandString(): string
    {
        return match (gettype($this->command)) {
            "string" => $this->command,
            "integer" | "double" => "[numeric] " . $this->command,
            "boolean" => "[boolean] " . ($this->command ? "true" : "false") . "]",
            "array" => "[array]",
            "object" => ($this->command instanceof Stringable ? (string) $this->command : "[" . get_class($this->command) . " object]"),
            "NULL" => "[null]",
            default => "[unknown type]"
        };
    }
}
