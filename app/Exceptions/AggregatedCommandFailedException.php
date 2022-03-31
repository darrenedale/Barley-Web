<?php

namespace App\Exceptions;

use Throwable;

/**
 * Exception thrown when an aggregated command fails and the AggregateCommand is set to abort on failure.
 */
class AggregatedCommandFailedException extends AggregatedCommandException
{
    /**
     * @var int The return code of the command that failed.
     */
    private int $m_returnCode;

    /**
     * @param int $returnCode
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $command, ?array $args, int $returnCode, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($command, $args, $message, $code, $previous);
        $this->m_returnCode = $returnCode;
    }

    /**
     * @return int The return code of the command that failed.
     */
    public function getReturnCode(): int
    {
        return $this->m_returnCode;
    }
}
