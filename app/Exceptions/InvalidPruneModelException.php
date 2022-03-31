<?php

namespace App\Exceptions;

use Throwable;

/**
 * Exception thrown when a model specified in a PruneCommand subclass is not a valid Model class.
 */
class InvalidPruneModelException extends PruneCommandException
{
    private string $m_model;

    /**
     * @param string $model The model class name that was found to be invalid.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $model, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->m_model = $model;
    }

    /**
     * @return string The model class name that was found to be invalid.
     */
    public function getModel(): string
    {
        return $this->m_model;
    }
}
