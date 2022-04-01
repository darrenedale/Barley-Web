<?php

namespace App\Generators;

use App\Util\Bitmap;
use App\Util\Colour;
use App\Util\Size;
use JetBrains\PhpStorm\Pure;
use RuntimeException;

class Code11BarcodeGenerator extends LinearBarcodeGenerator
{
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

    public static function isValidDigit(string $digit): bool
    {
        return str_contains(self::ValidDigits, $digit);
    }

    #[Pure] public static function typeCanEncode(string $data): bool
    {
        for ($idx = strlen($data) - 1; 0 <= $idx; --$idx) {
            if (!self::isValidDigit($data[$idx])) {
                return false;
            }
        }

        return true;
    }

    public function minimumSize(): Size
    {
        return new Size($this->minWidthForData(), 1);
    }

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

    private static function drawTerminator(Bitmap $bmp, int $offset = 0): void
    {
        $mask = 1 << (self::TerminatorPatternBits - 1);

        for ($idx = 0; $idx < self::TerminatorPatternBits; ++$idx) {
            $bmp->setPixel($offset + $idx, 0, (0 != (self::TerminatorPattern & $mask) ? Colour::BLACK : Colour::WHITE));
            $mask >>= 1;
        }
    }

    public function getBitmap(?Size $size): Bitmap
    {
        if (1 > $size->width || 1 > $size->height) {
            throw new RuntimeException("Invalid bitmap size.");
        }

        $minWidth = $this->minWidthForData();

        $bmp = Bitmap::createBitmap($minWidth, 1);
        $this->drawTerminator($bmp);
        $x = self::TerminatorPatternBits;

        foreach ($this->getDigitIndices() as $digitIndex) {
            $bmp->setPixel($x, 0, Colour::WHITE);    // spacer before digit
            ++$x;
            $mask = 1 << (self::DigitPatternBits[$digitIndex] - 1);
            $digit = self::DigitPatterns[$digitIndex];

            for ($bit = 0; $bit < self::DigitPatternBits[$digitIndex]; ++$bit) {
                $bmp->setPixel($x, 0, (0 != ($digit & $mask) ? Colour::BLACK : Colour::WHITE));
                ++$x;
                $mask >>= 1;
            }
        }

        $bmp->setPixel($x, 0, Colour::WHITE);    // spacer after final digit
        ++$x;
        $this->drawTerminator($bmp, $x);
        return Bitmap::createScaledBitmap($bmp, max($size->width, $minWidth), $size->height, false);
    }
}
