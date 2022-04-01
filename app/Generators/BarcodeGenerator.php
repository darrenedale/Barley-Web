<?php

namespace App\Generators;

use \App\Util\Size;
use \App\Util\Bitmap;
use JetBrains\PhpStorm\Pure;

abstract class BarcodeGenerator
{
    /**
     * @return \App\Util\Size The default size for a barcode bitmap.
     */
    #[Pure] public static final function defaultSize(): Size
    {
        return new Size(200, 75);
    }

    /**
     * The size for generated bitmaps.
     */
    private Size $m_size;

    /**
     * The data to encode in the barcode.
     */
    private string $m_data;

    /**
     * Default initialise a new generator.
     *
     * Only available to subclasses - this is an abstract base class.
     */
    protected function __constructor(Size | string $dataOrSize = "", Size $size = null)
    {
        if (is_string($dataOrSize)) {
            $data = $dataOrSize;

            if (!$size) {
                $size = self::defaultSize();
            }
        } else {
            $data = "";
            $size = $dataOrSize;
        }

        $this->setData($data);
        $this->setSize($size);
    }

    /**
     * Whether or not the generator can encode some given data.
     *
     * Not all barcode symbologies support all characters. If the symbology implemented by a
     * subclass has a restricted alphabet, this method checks whether a given string can be fully
     * represented by it.
     *
     * @param $data string The data to check.
     * @return true if the data can be fully encoded as a barcode, false otherwise.
     */
    public static function typeCanEncode(string $data): bool
    {
        return true;
    }

    /**
     * Whether or not the generator can encode some given data.
     *
     * If a symbology can be set up in multiple ways, this method will check whether the current
     * setup of the generator will allow the given string to be fully encoded.
     *
     * @param $data string The data to check.
     *
     * @return true if the data can be fully encoded as a barcode given the current configuration of
     * the generator, false otherwise.
     */
    public function canEncode(string $data): bool
    {
        return static::typeCanEncode($data);
    }

    /**
     * Fetch the data that the generator will encode in the barcode.
     *
     * @return string The data.
     */
    public function data(): string
    {
        return $this->m_data;
    }

    /**
     * The absolute minimum size that is necessary to render the data to a barcode bitmap.
     *
     * @return Size The minimum size.
     */
    public abstract function minimumSize(): Size;

    /**
     * Set the data to be encoded by the generator.
     *
     * If the provided data cannot be fully encoded by the generator, a RuntimeException is thrown.
     * Check with canEncode() first.
     *
     * @param $data string The data to encode.
     * @throws \InvalidArgumentException if the data cannot be encoded by the generator.
     */
    public function setData(string $data): void
    {
        if (!$this->canEncode($data)) {
            throw new \InvalidArgumentException("Provided data cannot be encoded by " . static::class);
        }

        $this->m_data = $data;
    }

    /**
     * Fetch the current bitmap size that the generator will produce.
     *
     * @return Size The size.
     */
    public function size(): Size
    {
        return $this->m_size;
    }

    /**
     * Set the bitmap size that the generator will produce.
     *
     * @param $size Size The desired size.
     *
     * @throws \InvalidArgumentException if the size has either dimension < 1.
     */
    public function setSize(Size $size): void
    {
        if (1 > $size->width || 1 > $size->height) {
            throw new \InvalidArgumentException("Size must have positive width and height.");
        }

        $this->m_size = $size;
    }

    /**
     * Generate a bitmap of the current data using a given size.
     *
     * Subclasses must implement this to generate barcode bitmaps. Subclasses must respect the size
     * requested by the client whenever possible. If the requested size is too small (e.g. there are
     * insufficient pixels horizontally to render all the required bars), the class may provide a
     * larger bitmap; but if the requested size is large enough to render the barcode, the requested
     * size should be used, even if the class thinks it may not be ideal (e.g. if the requested
     * width is not an integer multiple of the number of pixels required to render all the bars, the
     * class should not round the width up or down to the nearest multiple to prevent aliasing). In
     * short, trust the client-requested width unless it's impossible to do so.
     *
     * @param $size ?Size the desired size of the bitmap. Defaults to the size specified in the generator.
     *
     * @return Bitmap The generated bitmap.
     */
    public abstract function getBitmap(?Size $size): Bitmap;
}
