<?php

namespace App\Generators;

use App\Util\Bitmap;
use App\Util\Size;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;

/**
 * Generate an ITF barcode.
 *
 * This generates an interleaved 2 of 5 barcode for numeric data. Data with an odd number of digits
 * are left-padded with a single 0; therefore, barcodes always contain an even number of digits.
 * The barcode consists of a quiet zone (space) of a width equivalent to 10 narrow bars, the start
 * pattern, the encoded data, the checksum pattern, the stop pattern and another quiet zone
 * identical to the first. Wide bars/spaces are three times the width of narrow bars/spaces.
 *
 * ITF renders numeric data in pairs of digits. For each pair, the first digit is encoded in the
 * bars, the second in the spaces between the bars. Each digit has a 5-element pattern of wide and
 * narrow components, and each has two wide components and three narrow components.
 *
 * For example, in ITF 0 is represented as NARROW NARROW WIDE WIDE NARROW while 1 is represented by
 * WIDE NARROW NARROW NARROW WIDE. This means that the pair of digits 01 would be rendered to the
 * barcode as:
 *
 * NARROW BAR       (first component for 0 is NARROW)
 * WIDE SPACE       (first component for 1 is WIDE)
 * NARROW BAR       (second component fo 0 is NARROW)
 * NARROW SPACE     (second component for 1 is NARROW)
 * WIDE BAR         (third component for 0 is WIDE)
 * NARROW SPACE     (third component for 1 is NARROW)
 * WIDE             (fourth component for 0 is WIDE)
 * NARROW           (fourth component for 1 is NARROW)
 * NARROW           (fifth component for 0 is NARROW)
 * WIDE SPACE       (fifth component for 1 is WIDE)
 */
class ITFBarcodeGenerator extends LinearBarcodeGenerator
{
    /**
     * How many times wider is a wide bar/space than a thin bar/space
     */
    private const WideBarRatio = 3;
    private const WideBarMask = (1 << self::WideBarRatio) - 1;

    /**
     * Each digit is 2 thick plus 3 thin bars/spaces. thick bars are 3 times as wide as thin (see
     * WideBarRatio above), so each digit is therefore 9 px wide.
     */
    public const DigitExtent = 3 + (self::WideBarRatio * 2);
    public const ValidDigits = "0123456789";

    /**
     * The extent of the quiet zone at either end of the barcode.
     *
     * Expressed as a multiple of the width of a thin bar/space.
     */
    protected const QuietZoneExtent = 10;

    /**
     * The start pattern at the beginning of the barcode.
     */
    protected const StartPattern = 0b1010;
    protected const StartPatternExtent = 4;

    /**
     * The stop pattern at the end of the barcode.
     */
    protected const StopPattern = 0b11101;
    protected const StopPatternExtent = self::WideBarRatio + 2;

    /**
     * For each digit, a 1 is a wide bar/space, a 0 is a narrow bar/space.
     */
    protected const DigitPatterns = [
        0b00110,    // 0
        0b10001,    // 1
        0b01001,    // 2
        0b11000,    // 3
        0b00101,    // 4
        0b10100,    // 5
        0b01100,    // 6
        0b00011,    // 7
        0b10010,    // 8
        0b01010,    // 9
    ];

    /**
     * @var array<int, int>
     */
    protected static array $pairPatterns = [];

    /**
     * Check whether a character is a digit that can be encoded in an ITF barcode.
     *
     * @param $ch string The character to check. Must be exactly 1 character.
     *
     * @return bool Whether it can be encoded.
     */
    public static function isValidCharacter(string $ch): bool
    {
        return 1 === strlen($ch) && str_contains(self::ValidDigits, $ch);
    }

    /**
     * Check whether some given data is encodable using ITF symbology.
     *
     * ITF barcodes can only encode numeric data.
     *
     * @param $data string The data to check.
     *
     * @return bool Whether the data can be encoded in ITF symbology.
     */
    #[Pure] public static function typeCanEncode(string $data): bool
    {
        for ($idx = strlen($data) - 1; 0 <= $idx; --$idx) {
            if (!self::isValidCharacter($data[$idx])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the minimum size required to render the current data to an ITF barcode.
     *
     * The minimum height is always a single pixel, but this is not recommended since it won't scan
     * well with many readers.
     *
     * @return Size The minimum size.
     */
    #[Pure] public function minimumSize(): Size
{
        $length = strlen($this->data());

        // round up length to nearest multiple of 2 since we'll pad to the left with '0' if it has an odd number of
        // digits. +1 for the check digit
        return new Size(((1 + $length + ($length % 2)) * self::DigitExtent) + (2 * self::QuietZoneExtent) + self::StartPatternExtent + self::StopPatternExtent, 1);
    }

    /**
     * Helper to retrieve the bit pattern to render for a pair of digits.
     *
     * @param int digit1 The first digit in the pair.
     * @param int digit2 The second digit in the pair.
     *
     * @return int The bit pattern for the bars representing the digit pair.
     */
    protected static function getDigitPairPattern(int $digit1, int $digit2): int
    {
        $key = $digit1 * 10 + $digit2;

        if (isset(self::$pairPatterns[$key])) {
            //noinspection ConstantConditions
            return self::$pairPatterns[$key];
        }

        $digit1Pattern = self::DigitPatterns[$digit1];
        $digit2Pattern = self::DigitPatterns[$digit2];

        $outBit = self::DigitExtent * 2 - 1;
        $pattern = 0;
        $inMask = 0b10000;

        while (0 != $inMask) {
            if (0 != ($digit1Pattern & $inMask)) {
                // a wide bar
                $pattern |= (self::WideBarMask << ($outBit - (self::WideBarRatio - 1)));
                $outBit -= self::WideBarRatio;
            } else {
                // a narrow bar
                $pattern |= (1 << $outBit);
                --$outBit;
            }

            // interleaved space from digit 2
            $outBit -= (0 == ($digit2Pattern & $inMask) ? 1 : self::WideBarRatio);
            $inMask >>= 1;
        }

        self::$pairPatterns[$key] = $pattern;
        return $pattern;
    }

    /**
     * Generate a bitmap of the barcode for the data.
     *
     * @param $size Size | null the desired size of the bitmap.
     *
     * @return Bitmap The generated bitmap.
     */
    public function getBitmap(?Size $size): Bitmap
    {
        $minWidth = $this->minimumSize()->width;
        $bitmap = Bitmap::createBitmap($minWidth, 1);
        $data = $this->data();

        if (1 === strlen($data) % 2) {
            $data = "0{$data}";
        }

        self::renderPatternToBitmap($bitmap, 0, self::QuietZoneExtent, 0);
        $x = self::QuietZoneExtent;
        self::renderPatternToBitmap($bitmap, self::StartPattern, self::StartPatternExtent, $x);
        $x += self::StartPatternExtent;
        $checksum = 0;

        for ($idx = 0; $idx < strlen($data); $idx += 2) {
            $digit1 = ord($data[$idx]) - ord('0');
            $digit2 = ord($data[$idx + 1]) - ord('0');
            self::renderPatternToBitmap($bitmap, self::getDigitPairPattern($digit1, $digit2), 2 * self::DigitExtent, $x);
            $x += 2 * self::DigitExtent;
            $checksum += ($digit1 * 3) + $digit2;
        }

        $checksum %= 10;

        if (0 === $checksum) {
            self::renderPatternToBitmap($bitmap, self::DigitPatterns[$checksum], self::DigitExtent, $x);
        } else {
            self::renderPatternToBitmap($bitmap, self::DigitPatterns[10 - $checksum], self::DigitExtent, $x);
        }

        self::renderPatternToBitmap($bitmap, self::StopPattern, self::StopPatternExtent, $x);
        $x += self::StopPatternExtent;
        self::renderPatternToBitmap($bitmap, 0, self::QuietZoneExtent, $x);
        return Bitmap::createScaledBitmap($bitmap, max($size->width, $minWidth), $size->height, false);
    }
}
