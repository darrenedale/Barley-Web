<?php

namespace App\Exceptions;

use Throwable;

/**
 * Exception thrown when invalid configuration is encountered.
 */
class ConfigurationException extends \Exception
{
    /**
     * @var string The configuration file in which the error exists.
     */
    private string $m_file;

    /**
     * @var string The key of the configuration item that is in error, if available.
     */
    private string $m_key;

    /**
     * @param string $file The configuration file in which the error exists.
     * @param string $key The key of the configuration item that is in error, if available. Defaults to an empty string.
     * @param string $message An optional message explaining the error. Defaults to an empty string.
     * @param int $code An optional error code. Default is 0.
     * @param Throwable|null $previous The previous throwable that was thrown before this, if there is
     * one.
     */
    public function __construct(string $file, string $key = "", string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->m_file = $file;
        $this->m_key = $key;
    }

    /**
     * @return string The configuration file in which the error exists.
     */
    public function getConfigFile(): string
    {
        return $this->m_file;
    }

    /**
     * @return string The key of the configuration item that is in error, if available.
     */
    public function getConfigKey(): string
    {
        return $this->m_key;
    }
}
