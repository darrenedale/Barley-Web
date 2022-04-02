<?php

namespace App\Generators;

use App\Exceptions\InvalidDimensionException;
use App\Util\Bitmap;
use App\Util\Colour;
use App\Util\Size;
use JetBrains\PhpStorm\Pure;

/**
 * Generator for linear barcodes using Code 11 format.
 */
class Code11BarcodeGenerator extends LinearBarcodeGenerator
{
    // the full set of digits that Code 11 can encode
    public const ValidDigits = "0123456789-";

    // The pattern at the start and end of the barcode
    public const TerminatorPattern = 0b1011001;

    // The number of bits in the terminator pattern that represent bars or gaps
    public const TerminatorPatternBits = 7;

    // The bar patterns for each supported digit
    public const DigitPatterns = [
            0b101011,       // 0
            0b1101011,      // 1
            0b1001011,      // 2
            0b1100101,      // 3
            0b1011011,      // 4
            0b1101101,      // 5
            0b1001101,      // 6
            0b1010011,      // 7
            0b1101001,      // 8
            0b110101,       // 9
            0b101101,       // -
    ];

    // How many bits in the pattern represent a line or gap for each supported digit
    const DigitPatternBits = [6, 7, 7, 7, 7, 7, 7, 7, 7, 6, 6,];

    /**
     * @return string "code11"
     */
    public static function typeIdentifier(): string
    {
        return "code11";
    }

    /**
     * Check whether a digit can be encoded in a Code 11 barcode.
     *
     * @param string $digit A single-character string to check.
     *
     * @return bool true if the digit can be encoded, false if not.
     */
    public static function isValidDigit(string $digit): bool
    {
        assert(1 === strlen($digit), "isValidDigit() requires a single-character string as its only argument.");
        return str_contains(self::ValidDigits, $digit);
    }

    /**
     * Check whether Code11 can encode a given string as a barcode.
     *
     * @param string $data The data to check.
     *
     * @return bool true if the data can be encoded, false otherwise.
     */
    #[Pure] public static function typeCanEncode(string $data): bool
    {
        for ($idx = strlen($data) - 1; 0 <= $idx; --$idx) {
            if (!self::isValidDigit($data[$idx])) {
                return false;
            }
        }

        return true;
    }

    /**
     * The minimum size of bitmap required for the generator's current data.
     *
     * The height is always 1. The width is determined by the data to encode.
     *
     * @return \App\Util\Size The minimum size.
     */
    public function minimumSize(): Size
    {
        return new Size($this->minWidthForData(), 1);
    }

    /**
     * Helper to calculate the minimum bitmap width required to accurately encode the current data.
     *
     * @return int The minimum width.
     */
    #[Pure] private function minWidthForData(): int
    {
        $min = 2 * self::TerminatorPatternBits;
        $data = $this->getDigitIndices();

        if (0 < count($data)) {
            foreach ($data as $digitIndex) {
                // gap before each digit
                $min += self::DigitPatternBits[$digitIndex] + 1;
            }

            // gap before terminator pattern
            $min += 1;
        }

        return $min;
    }

    /**
     * Helper to convert the current data to an array of digit indices.
     *
     * The indices in the returned array can be used with the DigitPatterns array to get the patterns to render into the
     * bitmap.
     *
     * @return array<int> The indices of the digits in the current data.
     */
    #[Pure] private function getDigitIndices(): array
    {
        $data = str_split($this->data());

        for ($idx = 0; $idx < count($data); ++$idx) {
            if ('-' == $data[$idx]) {
                $data[$idx] = 10;
            } else {
                $data[$idx] = ord($data[$idx]) - ord('0');
            }
        }

        return $data;
    }

    /**
     * Get a bitmap of the data encoded as a Code 11 barcode.
     *
     * The returned bitmap may be wider than the requested size if the barcode can't be accurately rendered with the
     * requested width.
     *
     * @param \App\Util\Size|null $size The optional size for the final bitmap. Defaults to the size set in the
     * generator.
     *
     * @return \App\Util\Bitmap The bitmap.
     * @throws \App\Exceptions\InvalidDimensionException if either of the dimensions in the requested bitmap size is
     * < 1.
     */
    public function getBitmap(?Size $size = null): Bitmap
    {
        if (!isset($size)) {
            // instance size can't be set to an invalid value
            $size = $this->size();
        } else {
            $this->validateSize($size);
        }

        $minWidth = $this->minWidthForData();
        $bmp = Bitmap::createBitmap($minWidth, 1);
        self::renderPatternToBitmap($bmp, self::TerminatorPattern, self::TerminatorPatternBits, 0);
        $x = self::TerminatorPatternBits;

        foreach ($this->getDigitIndices() as $digitIndex) {
            $bmp->setPixel($x, 0, Colour::WHITE);    // spacer before digit
            ++$x;
            self::renderPatternToBitmap($bmp, self::DigitPatterns[$digitIndex], self::DigitPatternBits[$digitIndex], $x);
            $x += self::DigitPatternBits[$digitIndex];
        }

        $bmp->setPixel($x, 0, Colour::WHITE);    // spacer after final digit
        ++$x;
        self::renderPatternToBitmap($bmp, self::TerminatorPattern, self::TerminatorPatternBits, 0);
        return Bitmap::createScaledBitmap($bmp, max($size->width, $minWidth), $size->height, false);
    }
}
