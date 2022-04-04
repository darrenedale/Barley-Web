<?php

namespace App\Exceptions;

use App\Generators\BarcodeGenerator;
use Exception;
use Throwable;

class InvalidDataException extends BarcodeGeneratorException
{
    private string $m_data;
    private string $m_barcodeType;

    /**
     * Initialise a new InvalidDataException.
     *
     * @param string $data The data that is not valid.
     * @param string|BarcodeGenerator $type The type of barcode for which the data is not valid.
     * @param string $message The optional description of the error. Default is an empty string.
     * @param int $code The optional error code. Default is 0.
     * @param \Throwable|null $previous The optional previous Throwable. Default is null.
     */
    public function __construct(string $data, string | BarcodeGenerator $type, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->m_data = $data;

        try {
            $this->m_barcodeType = ($type instanceof BarcodeGenerator ? $type->typeIdentifier() : $type);
        } catch (Exception) {
            $this->m_barcodeType = "<unknown>";
        }
    }

    /**
     * Fetch the data that is not valid for the barcode generator.
     *
     * @return string The invalid data.
     */
    public function getData(): string
    {
        return $this->m_data;
    }

    /**
     * Fetch the type of barcode for which the data is not valid.
     *
     * @return string The barcode type identifier.
     */
    public function getBarcodeType(): string
    {
        return $this->m_barcodeType;
    }
}
