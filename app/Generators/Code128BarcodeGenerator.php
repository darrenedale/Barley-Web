<?php

namespace App\Generators;

use App\Exceptions\InvalidDimensionException;
use App\Util\Bitmap;
use App\Util\Size;

/**
 * Generator for Code 128 barcodes.
 *
 * See https://en.wikipedia.org/wiki/Code_128
 */
class Code128BarcodeGenerator extends LinearBarcodeGenerator
{
    /**
     * Enumeration of the codesets for Code128 barcodes.
     */
    protected const CodeSetA = 0;
    protected const CodeSetB = 1;
    protected const CodeSetC = 2;

    /**
     * The number of bar widths in the quiet zones.
     */
    private const QuietZoneExtent = 10;

    /**
     * The number of bits in each symbol (equates to the number of bar widths).
     */
    private const SymbolBits = 11;

    /**
     * The number of bits in the full stop pattern (equates to the number of bar widths).
     */
    private const StopBits = 13;

    /**
     * Index of the symbol to start the barcode with codeset A.
     */
    private const StartCodeASymbolIndex = 103;

    /**
     * Index of the symbol to start the barcode with codeset B.
     */
    private const StartCodeBSymbolIndex = 104;

    /**
     * Index of the symbol to start the barcode with codeset C.
     */
    private const StartCodeCSymbolIndex = 105;

    /**
     * Index of the symbol to shift from codeset between codesets a and b for the next symbol.
     */
    private const ShiftSymbolIndex = 98;

    /**
     * Index of the symbol to transition from codeset A to B.
     */
    private const AToBSymbolIndex = 100;

    /**
     * Index of the symbol to transition from codeset A to C.
     */
    private const AToCSymbolIndex = 99;

    /**
     * Index of the symbol to transition from codeset B to A.
     */
    private const BToASymbolIndex = 101;

    /**
     * Index of the symbol to transition from codeset B to C.
     */
    private const BToCSymbolIndex = 99;

    /**
     * Index of the symbol to transition from codeset C to A.
     */
    private const CToASymbolIndex = 101;

    /**
     * Index of the symbol to transition from codeset C to B.
     */
    private const CToBSymbolIndex = 100;

    /**
     * The full stop pattern, including the two terminating bars.
     */
    private const StopSymbol = 0b1100011101011;

    /**
     * The characters in codeset A, in symbol order.
     */
    private const CodesetACharacters = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1a\x1b\x1c\x1d\x1e\x1f";

    /**
     * The characters in codeset B, in symbol order.
     */
    private const CodesetBCharacters = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~\x7f";

    /**
     * The full set of Code128 Symbol patterns.
     */
    private const Symbols = [
        // pattern    codeSet A | B | C
        0b11011001100,  // space | space | 00
        0b11001101100,  // ! | ! | 01
        0b11001100110,  // " | " | 02
        0b10010011000,  // # | # | 03
        0b10010001100,  // $ | $ | 04
        0b10001001100,  // % | % | 05
        0b10011001000,  // & | & | 06
        0b10011000100,  // ' | ' | 07
        0b10001100100,  // ( | ( | 08
        0b11001001000,  // ) | ) | 09
        0b11001000100,  // * | * | 10
        0b11000100100,  // + | + | 11
        0b10110011100,  // , | , | 12
        0b10011011100,  // - | - | 13
        0b10011001110,  // . | . | 14
        0b10111001100,  // / | / | 15
        0b10011101100,  // 0 | 0 | 16
        0b10011100110,  // 1 | 1 | 17
        0b11001110010,  // 2 | 2 | 18
        0b11001011100,  // 3 | 3 | 19
        0b11001001110,  // 4 | 4 | 20
        0b11011100100,  // 5 | 5 | 21
        0b11001110100,  // 6 | 6 | 22
        0b11101101110,  // 7 | 7 | 23
        0b11101001100,  // 8 | 8 | 24
        0b11100101100,  // 9 | 9 | 25
        0b11100100110,  // : | : | 26
        0b11101100100,  // ; | ; | 27
        0b11100110100,  // < | < | 28
        0b11100110010,  // = | = | 29
        0b11011011000,  // > | > | 30
        0b11011000110,  // ? | ? | 31
        0b11000110110,  // @ | @ | 32
        0b10100011000,  // A | A | 33
        0b10001011000,  // B | B | 34
        0b10001000110,  // C | C | 35
        0b10110001000,  // D | D | 36
        0b10001101000,  // E | E | 37
        0b10001100010,  // F | F | 38
        0b11010001000,  // G | G | 39
        0b11000101000,  // H | H | 40
        0b11000100010,  // I | I | 41
        0b10110111000,  // J | J | 42
        0b10110001110,  // K | K | 43
        0b10001101110,  // L | L | 44
        0b10111011000,  // M | M | 45
        0b10111000110,  // N | N | 46
        0b10001110110,  // O | O | 47
        0b11101110110,  // P | P | 48
        0b11010001110,  // Q | Q | 49
        0b11000101110,  // R | R | 50
        0b11011101000,  // S | S | 51
        0b11011100010,  // T | T | 52
        0b11011101110,  // U | U | 53
        0b11101011000,  // V | V | 54
        0b11101000110,  // W | W | 55
        0b11100010110,  // X | X | 56
        0b11101101000,  // Y | Y | 57
        0b11101100010,  // Z | Z | 58
        0b11100011010,  // [ | [ | 59
        0b11101111010,  // \ | \ | 60
        0b11001000010,  // ] | ] | 61
        0b11110001010,  // ^ | ^ | 62
        0b10100110000,  // _ | _ | 63
        0b10100001100,  // NUL | ` | 64
        0b10010110000,  // SOH | a | 65
        0b10010000110,  // STX | b | 66
        0b10000101100,  // ETX | c | 67
        0b10000100110,  // EOT | d | 68
        0b10110010000,  // ENQ | e | 69
        0b10110000100,  // ACK | f | 70
        0b10011010000,  // BEL | g | 71
        0b10011000010,  // BS | h | 72
        0b10000110100,  // HT | i | 73
        0b10000110010,  // LF | j | 74
        0b11000010010,  // VT | k | 75
        0b11001010000,  // FF | l | 76
        0b11110111010,  // CR | m | 77
        0b11000010100,  // SO | n | 78
        0b10001111010,  // SI | o | 79
        0b10100111100,  // DLE | p | 80
        0b10010111100,  // DC1 | q | 81
        0b10010011110,  // DC2 | r | 82
        0b10111100100,  // DC3 | s | 83
        0b10011110100,  // DC4 | t | 84
        0b10011110010,  // NAK | u | 85
        0b11110100100,  // SYN | v | 86
        0b11110010100,  // ETB | w | 87
        0b11110010010,  // CAN | x | 88
        0b11011011110,  // EM | y | 89
        0b11011110110,  // SUB | z | 90
        0b11110110110,  // ESC | { | 91
        0b10101111000,  // FS | | | 92
        0b10100011110,  // GS | } | 93
        0b10001011110,  // RS | ~ | 94
        0b10111101000,  // US | DEL | 95
        0b10111100010,  // FNC3 | FNC3 | 96
        0b11110101000,  // FNC2 | FNC2 | 97
        0b11110100010,  // Shift B | Shift A | 98
        0b10111011110,  // Code C | Code C | 99
        0b10111101110,  // Code B | FNC 4 | Code B
        0b11101011110,  // FNC4 | Code A | Code A
        0b11110101110,  // FNC 1 | FNC 1 | FNC 1
        0b11010000100,  // Start Code A
        0b11010010000,  // Start Code B
        0b11010011100,  // Start Code C
        0b11000111010,  // Stop Code
    ];

    /**
     * Tracks the state while a bitmap is being generated.
     *
     * At other times, this is null.
     */
    private mixed $m_renderState;

    /**
     * @return string "code128"
     */
    public static function typeIdentifier(): string
    {
        return "code128";
    }

    /**
     * Check whether a string can be encoded as a Code128 barcode.
     *
     * All ASCII characters are supported.
     *
     * @param $data string The data to check.
     *
     * @return true if the data contains only ASCII characters, false otherwise.
     */
    public static function typeCanEncode(string $data): bool
    {
        for ($idx = strlen($data) - 1; 0 <= $idx; --$idx) {
            if (127 < ord($data[$idx])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Set up the initial state for rendering.
     *
     * If the provided Bitmap is null, the render is a dry-run.
     *
     * @param \App\Util\Bitmap|null $bitmap The bitmap being rendered to.
     *
     * @return void
     */
    private function startRender(?Bitmap $bitmap = null): void
    {
        $this->m_renderState = new class
        {
            public ?int $currentCodeSet = null;  // which codeset is currently being used to render symbols
            public int $checksum = 0;               // the cumulative checksum of symbols written to the barcode
            public int $checksumWeight = 1;         // the checksum weight of the next symbol written to the barcode
            public int $x = 0;                      // the x-coordinate of the next bar/gap
            public ?Bitmap $bitmap;                  // the bitmap being rendered to
        };

        $this->m_renderState->bitmap = $bitmap;
    }

    /**
     * Free the current render state.
     */
    private function finishRender(): void
    {
        $this->m_renderState = null;
    }

    /**
     * @return Size The minimum size of Bitmap required to render the barcode.
     */
    public function minimumSize(): Size
    {
        // don't set bitmap so we do a dry-run render that will just update state
        $this->startRender();
        $this->renderBarcode();
        $width               = $this->m_renderState->x;
        $this->finishRender();
        return new Size($width, 1);
    }

    /**
     * Determine the length of the sequence of numeric digits in a string from a given offset.
     *
     * This is the number of characters between the offset and the first character thereafter that
     * is not a numeric digit. This will be 0 if the character at the offset is not a numeric
     * digit.
     *
     * This is used as a helper to determine when it's useful to switch to Code C in the rendered
     * barcode.
     *
     * @param $data string The string to examine.
     * @param $start int  The offset at which to start looking.
     *
     * @return int The number of numeric digits in the discovered sequence.
     */
    private static function numericSequenceLength(string $data, int $start): int
    {
        $end = $start;
        $length = strlen($data);

        while ($end < $length && ctype_digit($data[$end])) {
            ++$end;
        }

        return $end - $start;
    }

    /**
     * Determine the length of the longest sequence of characters from codeset A in a string from a
     * given offset.
     *
     * This is the number of characters between the offset and the first character thereafter that
     * is not in codeset A. This will be 0 if the character at the offset is not in codeset A.
     *
     * This is used as a helper to determine when it's useful to switch to Code A in the rendered
     * barcode.
     *
     * @param $data string The string to examine.
     * @param $start int The offset at which to start looking.
     *
     * @return int The number of characters from codeset A in the discovered sequence.
     */
    private static function codesetASequenceLength(string $data, int $start): int
    {
        $end = $start;
        $length = strlen($data);

        while ($end < $length && false != strpos(self::CodesetACharacters, $data[$end])) {
            ++$end;
        }

        return $end - $start;
    }

    /**
     * Determine the length of the longest sequence of characters from codeset B in a string from a
     * given offset.
     *
     * This is the number of characters between the offset and the first character thereafter that
     * is not in codeset B. This will be 0 if the character at the offset is not in codeset B.
     *
     * This is used as a helper to determine when it's useful to switch to Code A in the rendered
     * barcode.
     *
     * @param $data string The string to examine.
     * @param $start int The offset at which to start looking.
     *
     * @return int The number of characters from codeset B in the discovered sequence.
     */
    private static function codesetBSequenceLength(string $data, int $start): int
    {
        $end = $start;
        $length = strlen($data);

        while ($end < $length && false != strpos(self::CodesetBCharacters, $data[$end])) {
            ++$end;
        }

        return $end - $start;
    }

    /**
     * Determine the index of the symbol representing a character in codeset A.
     *
     * @param $ch string The character to find.
     *
     * @return int | false The index of the symbol, or false if it's not in Code A.
     */
    private static function codeASymbolIndex(string $ch): int|false
    {
        return strpos(self::CodesetACharacters, $ch);
    }

    /**
     * Determine the index of the symbol representing a character in codeset B.
     *
     * @param $ch string The character to find.
     *
     * @return int | false The index of the symbol, or -1 if it's not in Code B.
     */
    private static function codeBSymbolIndex(string $ch): int|false
    {
        return strpos(self::CodesetBCharacters, $ch);
    }

    /**
     * Determine the index of the symbol representing a character from codeset A or B.
     *
     * @param $codeSet int The codeset to look into.
     * @param $ch string The character to look up.
     *
     * @return int|false The index or false if the character is not in the codeset.
     */
    private static function symbolIndex(int $codeSet, string $ch): int|false
    {
        assert(self::CodeSetA == $codeSet || self::CodeSetB == $codeSet, "invalid codeset for symbol lookup - must be A or B for single-character symbol lookup");
        return self::CodeSetA == $codeSet ? self::codeASymbolIndex($ch) : self::codeBSymbolIndex($ch);
    }

    /**
     * Determine the index of the symbol representing a pair of digits in Code C.
     *
     * The provided string must consist of precisely two numeric digits.
     *
     * @param $digits string The two-digit sequence to find.
     *
     * @return int The index of the symbol.
     */
    private static function codeCSymbolIndex(string $digits): int
    {
        return (int) $digits;
    }

    /**
     * Render a bit pattern to the barcode.
     *
     * If the current render state has no bitmap, this method just does all the necessary
     * calculations and updates the state without actually doing any rendering. This is useful for
     * dru runs (e.g. when calculating the minimum width necessary for the barcode representation of
     * some data).
     *
     * @param $pattern int The bit pattern to render.
     * @param $bits int The number of rightmost bits in the pattern to render.
     */
    private function renderPattern(int $pattern, int $bits): void
    {
        // check whether it's a dry-run (e.g. to just calculate min width)
        if (null != $this->m_renderState->bitmap) {
            self::renderPatternToBitmap($this->m_renderState->bitmap, $pattern, $bits, $this->m_renderState->x);
        }

        $this->m_renderState->x += $bits;
    }

    /**
     * Render a symbol to the Bitmap currently being rendered
     *
     * @param $symbolIndex int The index of the symbol to render. Must be >= 0 and <= 106.
     */
    private function renderSymbol(int $symbolIndex): void
    {
        $this->renderPattern(self::Symbols[$symbolIndex], self::SymbolBits);
        $this->m_renderState->checksum += ($symbolIndex * $this->m_renderState->checksumWeight);
        ++$this->m_renderState->checksumWeight;
    }

    /**
     * Render a symbol preceded by the shift symbol to the Bitmap currently being rendered.
     *
     * @param $symbolIndex int The symbol to render after the shift symbol.
     */
    private function renderShiftedSymbol(int $symbolIndex): void
    {
        assert($this->m_renderState->currentCodeSet == self::CodeSetA || $this->m_renderState->currentCodeSet == self::CodeSetB, "shifted symbols can only be rendered from within codeset a or b");
        $this->renderSymbol(self::ShiftSymbolIndex);
        $this->renderSymbol($symbolIndex);
    }

    /**
     * Render the quiet zone to the Bitmap currently being rendered.
     *
     * The specification requires the quiet zone to be 10 units or more. It is currently fixed at 10
     * units in this implementation.
     */
    private function renderQuietZone(): void
    {
        // if QuietZoneExtent is ever made larger than an int, this will need a loop
        $this->renderPattern(0, self::QuietZoneExtent);
    }

    /**
     * Render the start pattern for one of the codesets to the Bitmap currently being rendered.
     *
     * @param $codeSet int The codeset whose start pattern should be rendered.
     */
    private function renderCodeStart(int $codeSet): void
    {
        $this->renderSymbol(match ($codeSet) {
            self::CodeSetA => self::StartCodeASymbolIndex,
            self::CodeSetB => self::StartCodeBSymbolIndex,
            self::CodeSetC => self::StartCodeCSymbolIndex,
            default => assert(false, "Invalid Code for Code 128 barcode start pattern"),
        });

        $this->m_renderState->currentCodeSet = $codeSet;

        // when the start pattern is drawn the checksum weight remains 1 for the first data pattern
        --$this->m_renderState->checksumWeight;
    }

    /**
     * If necessary, render the symbol to change from the current codeset to a new codeset.
     *
     * If the rendering state is already using the correct codeset, this is a no-op. If the state
     * is yet to start rendering in any codeset, a codeSet start symbol is rendered; otherwise the
     * appropriate codeSet switching symbol for the current codeset is rendered, the render state
     * is updated to reflect the new codeset, and the checksum is updated.
     *
     * @param $to int The codeSet to change to.
     */
    private function renderCodeChange(int $to): void
    {
        if (null == $this->m_renderState->currentCodeSet) {
            $this->renderCodeStart($to);
            return;
        }

        if ($to == $this->m_renderState->currentCodeSet) {
            return;
        }

        if (self::CodeSetA == $this->m_renderState->currentCodeSet) {
            if (self::CodeSetB == $to) {
                $symbolIndex = self::AToBSymbolIndex;
            } else {
                $symbolIndex = self::AToCSymbolIndex;
            }
        } else {
            if (self::CodeSetB == $this->m_renderState->currentCodeSet) {
                if (self::CodeSetA == $to) {
                    $symbolIndex = self::BToASymbolIndex;
                } else {
                    $symbolIndex = self::BToCSymbolIndex;
                }
            } else {
                if (self::CodeSetA == $to) {
                    $symbolIndex = self::CToASymbolIndex;
                } else {
                    $symbolIndex = self::CToBSymbolIndex;
                }
            }
        }

        $this->renderSymbol($symbolIndex);
        $this->m_renderState->currentCodeSet = $to;
    }

    /**
     * Render the checksum symbol to the Bitmap currently being rendered.
     */
    private function renderChecksum(): void
    {
        $this->renderPattern(self::Symbols[$this->m_renderState->checksum % 103], self::SymbolBits);
    }

    /**
     * Render the stop symbol to the Bitmap currently being rendered.
     */
    private function renderStop(): void
    {
        $this->renderPattern(self::StopSymbol, self::StopBits);
    }

    /**
     * The algorithm to render the data as a Code 128 barcode.
     *
     * This method requires m_renderState to be initialised. It may have a null bitmap, in which
     * case the rendering will be a dry run - all the necessary calculations will be performed, but
     * no bitmap will be written.
     */
    private function renderBarcode(): void
    {
        $data      = $this->data();
        $length    = strlen($data);
        $charIndex = 0;
        $this->renderQuietZone();

        while ($charIndex < $length) {
            // check whether we can save some space using codeset C with a sequence of numeric
            // digits
            $sequenceLength = self::numericSequenceLength($data, $charIndex);

            if ((3 < $sequenceLength && 0 == $charIndex) ||                               // initial sequence of 4 or more numeric digits
                (3 < $sequenceLength && $length - $sequenceLength == $charIndex) ||     // final sequence of 4 or more numeric digits
                5 < $sequenceLength) {                                               // a sequence of 6 or more numeric digits elsewhere

                if (1 == $sequenceLength % 2) {
                    // sequence has odd length, so one of the numeric digits must be rendered
                    // in codeset A or B
                    if (null == $this->m_renderState->currentCodeSet) {
                        // sequence is at the start of the code so start with codeset A
                        $this->renderCodeChange(self::CodeSetA);
                    }

                    // we should never be in Code C at this point, should always be in
                    // either A or B
                    assert(self::CodeSetA == $this->m_renderState->currentCodeSet || self::CodeSetB == $this->m_renderState->currentCodeSet, "must be in codeset A or B when rendering first digit of a sequence of numeric digits with an odd length");
                    $this->renderSymbol(self::symbolIndex($this->m_renderState->currentCodeSet, $data[$charIndex]));
                    ++$charIndex;
                    --$sequenceLength;
                }

                // render a sequence of Code C
                $this->renderCodeChange(self::CodeSetC);

                while (0 < $sequenceLength) {
                    $this->renderSymbol($this->codeCSymbolIndex(substr($data, $charIndex, 2)));
                    $charIndex      += 2;
                    $sequenceLength -= 2;
                }
            } else {
                // if not, determine whether to use codeset a or b based on which yields the
                // longest sequence of characters uninterrupted by a shift or codeset change
                $aSequenceLength = self::codesetASequenceLength($data, $charIndex);
                $bSequenceLength = self::codesetBSequenceLength($data, $charIndex);

                if ($aSequenceLength > $bSequenceLength) {
                    $sequenceCodeSet = self::CodeSetA;
                    $sequenceLength  = $aSequenceLength;
                } else {
                    $sequenceCodeSet = self::CodeSetB;
                    $sequenceLength  = $bSequenceLength;
                }

                if (1 == $sequenceLength &&                                  // if it's a single character
                    $this->m_renderState->currentCodeSet != $sequenceCodeSet &&  // that's not in the current codeset
                    self::CodeSetC != $this->m_renderState->currentCodeSet &&        // and the current codeset is A or B
                    null != $this->m_renderState->currentCodeSet) {             // (which also means it's not the first character in the data)
                    // rendering shifted symbol rather than to & from code changes saves a symbol
                    $this->renderShiftedSymbol(self::symbolIndex($sequenceCodeSet, $data[$charIndex]));
                    ++$charIndex;
                } else {
                    // otherwise render a codeset change then the symbols for the sequence
                    $this->renderCodeChange($sequenceCodeSet);

                    while (0 < $sequenceLength) {
                        $this->renderSymbol(self::symbolIndex($sequenceCodeSet, $data[$charIndex]));
                        --$sequenceLength;
                        ++$charIndex;
                    }
                }
            }
        }

        $this->renderChecksum();
        $this->renderStop();
        $this->renderQuietZone();
    }

    /**
     * Render the data as a Code128 barcode to a Bitmap.
     *
     * @param $size ?Size the desired size of the bitmap.
     *
     * @return Bitmap The generated bitmap.
     * @throws \App\Exceptions\InvalidDimensionException if either of the dimensions in the requested bitmap size is
     * < 1.
     */
    public function getBitmap(?Size $size = null): Bitmap
    {
        if (!isset($size)) {
            $size = $this->size();
        } else {
            $this->validateSize($size);
        }

        $minWidth = $this->minimumSize()->width;
        $this->startRender(Bitmap::createBitmap($minWidth, 1));
        $this->renderBarcode();
        $ret = Bitmap::createScaledBitmap($this->m_renderState->bitmap, max($size->width, $minWidth), $size->height, false);
        $this->finishRender();
        return $ret;
    }
}
