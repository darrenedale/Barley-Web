<?php

namespace App\Exceptions;

use JetBrains\PhpStorm\Pure;
use Throwable;

/**
 * Abstract base class for exceptions thrown during execution of an AggregateCommand.
 */
abstract class AggregatedCommandException extends \Exception
{
    /**
     * @var string The command that was aggregated that triggered the exception.
     */
    private string $m_command;

    /**
     * @var array|null The args to the command that was aggregated that triggered the exception.
     *
     * Will be null if there were no arguments.
     */
    private ?array $m_args;

    /**
     * @param string $command The command that threw.
     * @param array|null $args The args to the command that threw.
     * @param string $message An optional message indicating what happened. Default is an empty string.
     * @param int $code An optional error code. Default is 0.
     * @param \Throwable | null $previous An optional previous exception, if one exists.
     *
     */
    #[Pure] public function __construct(string $command, ?array $args, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->m_command = $command;
        $this->m_args = $args;
    }

    /**
     * @return string The command that threw.
     */
    public function getCommand(): string
    {
        return $this->m_command;
    }

    /**
     * @return array|null The args for the command that threw.
     */
    public function getArguments(): ?array
    {
        return $this->m_args;
    }

}
