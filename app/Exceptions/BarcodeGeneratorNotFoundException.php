<?php

namespace App\Exceptions;

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;
use Throwable;

/**
 * Exception thrown by the factory when a barcode type has no discovered generator.
 */
class BarcodeGeneratorNotFoundException extends BarcodeGeneratorException
{
    private string $m_type;

    /**
     * Initialise a new exception instance.
     *
     * @param string $type The barcode type for which a generator was sought.
     * @param string $message The message for the exception, if there is one.
     * @param int $code The exception code, if there is one.
     * @param Throwable|null $previous The previous exception, if there is one.
     */
    public function __construct(string $type, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->m_type = $type;
    }

    /**
     * @return string The barcode type for which a generator could not be discovered.
     */
    public function getType(): string
    {
        return $this->m_type;
    }
}
