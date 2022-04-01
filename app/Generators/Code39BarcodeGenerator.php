<?php

namespace App\Generators;

use App\Util\Bitmap;
use App\Util\Colour;
use App\Util\Size;
use JetBrains\PhpStorm\Pure;
use RuntimeException;

class Code39BarcodeGenerator extends LinearBarcodeGenerator
{
    // The CODE39 dictionary
    public const ValidDigits = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 -$%./+";

    // The pattern at the start and end of the barcode
    public const TerminatorPattern = 0b100101101101;

    // The bar patterns for each supported digit
    public const DigitPatterns = [
        0b101001101101,     // 0
        0b110100101011,     // 1
        0b101100101011,     // 2
        0b110110010101,     // 3
        0b101001101011,     // 4
        0b110100110101,     // 5
        0b101100110101,     // 6
        0b101001011011,     // 7
        0b110100101101,     // 8
        0b101100101101,     // 9
        0b110101001011,     // A
        0b101101001011,     // B
        0b110110100101,     // C
        0b101011001011,     // D
        0b110101100101,     // E
        0b101101100101,     // F
        0b101010011011,     // G
        0b110101001101,     // H
        0b101101001101,     // I
        0b101011001101,     // J
        0b110101010011,     // K
        0b101101010011,     // L
        0b110110101001,     // M
        0b101011010011,     // N
        0b110101101001,     // O
        0b101101101001,     // P
        0b101010110011,     // Q
        0b110101011001,     // R
        0b101101011001,     // S
        0b101011011001,     // T
        0b110010101011,     // U
        0b100110101011,     // V
        0b110011010101,     // W
        0b100101101011,     // X
        0b110010110101,     // Y
        0b100110110101,     // Z
        0b100110101101,     // space
        0b100101011011,     // -
        0b100100100101,     // $
        0b101001001001,     // %
        0b110010101101,     // .
        0b100100101001,     // /
        0b100101001001,     // +
    ];

    // How many bits in each digit pattern
    public const PatternBits = 12;

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

    #[Pure] public function minimumSize(): Size
    {
        return new Size($this->minWidthForData(), 1);
    }

    #[Pure] private function minWidthForData(): int
    {
        $digitCount = strlen($this->data());
        // 12 pixels per digit, plus 12 each for the terminators, plus one pixel between each
        // digit and between the first/last digit and the terminator
        return (($digitCount + 2) * self::PatternBits) + $digitCount + 1;
    }

    /**
     * Convert the data into indices into the DigitPatterns array.
     *
     * @return array<int> The array of indices.
     */
    #[Pure] private function getDigitIndices(): array
    {
        $data = str_split($this->data());

        for ($idx = 0; $idx < count($data); ++$idx) {
            $data[$idx] = ord($data[$idx]);

            if (ord('0') <= $data[$idx] && ord('9') >= $data[$idx]) {
                $data[$idx] = $data[$idx] - ord('0');
            } else {
                if (ord('A') <= $data[$idx] && ord('Z') >= $data[$idx]) {
                    $data[$idx] = 10 + $data[$idx] - ord('A');
                } else {
                    $data[$idx] = match ($data[$idx]) {
                        ord(' ') => 36,
                        ord('-') => 37,
                        ord('$') => 38,
                        ord('%') => 39,
                        ord('.') => 40,
                        ord('/') => 41,
                        ord('+') => 42,
                    };
                }
            }
        }

        return $data;
    }

    private static function drawTerminator(Bitmap $bmp, int $offset = 0): void
    {
        $mask = 1 << (self::PatternBits - 1);

        for ($idx = 0; $idx < self::PatternBits; ++$idx) {
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
        $x = self::PatternBits;

        foreach ($this->getDigitIndices() as $digitIndex) {
            $bmp->setPixel($x, 0, Colour::WHITE);    // spacer before digit
            ++$x;
            $mask  = 1 << (self::PatternBits - 1);
            $digit = self::DigitPatterns[$digitIndex];

            for ($bit = 0; $bit < self::PatternBits; ++$bit) {
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
