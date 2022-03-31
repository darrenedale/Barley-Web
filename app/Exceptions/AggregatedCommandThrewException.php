<?php

namespace App\Exceptions;

use JetBrains\PhpStorm\Pure;
use Throwable;

/**
 * Exception thrown when an AggregateCommand encounters a command that throws an exception.
 */
class AggregatedCommandThrewException extends AggregatedCommandException
{
    /**
     * @param string $command The command that threw.
     * @param array|null $args The args to the command that threw.
     * @param \Throwable $thrownException The exception that the aggregated command threw.
     * @param string $message An optional message indicating what happened. Default is an empty string.
     * @param int $code An optional error code. Default is 0.
     */
    #[Pure] public function __construct(string $command, ?array $args, Throwable $thrownException, string $message = "", int $code = 0) {
        parent::__construct($command, $args, $message, $code, $thrownException);
    }

    /**
     * @return \Throwable The Throwable thrown by the aggregated command.
     */
    #[Pure] public function getThrowable(): Throwable
    {
        return $this->getPrevious();
    }
}
