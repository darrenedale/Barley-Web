<?php

namespace App\Util;

use App\Exceptions\InvalidColourComponentException;

/**
 * A 32-bit colour in the ARGB colour space.
 *
 * Transitional class to aid the port of barcode generators from the Android app.
 */
class Colour
{
    public const WHITE = 0xffffffff;
    public const BLACK = 0xff000000;

    /**
     * Pack an ARGB colour into a 32-bit integer.
     *
     * @param $red int The red component. Must be between 0 and 255 inclusive.
     * @param $green int The green component. Must be between 0 and 255 inclusive.
     * @param $blue int The blue component. Must be between 0 and 255 inclusive.
     * @param $alpha int The alpha component. Must be between 0 (transparent) and 255 (opaque) inclusive. Defaults to
     * 255 (fully opaque).
     *
     * @return int The provided colour as a 32-bit ARGB int.
     * @throws \App\Exceptions\InvalidColourComponentException
     */
    public static function fromRgba(int $red, int $blue, int $green, int $alpha = 0xff): int
    {
        foreach (["red", "green", "blue", "alpha"] as $component) {
            if (0x00 > $$component || 0xff < $$component) {
                throw new InvalidColourComponentException($$component, $component, "The {$component} component for a colour must be between 0 and 255.");
            }
        }

        return ($alpha << 24) | ($red << 16) | ($green << 8) | $blue;
    }

    /**
     * Unpack a 32-bit colour to its ARGB components.
     *
     * The first eith bits represent the alpha, the next eight the red, the next eight the green and the final eight the
     * blue:
     *
     * bit: 31 ............................... 0
     *       AAAAAAAA RRRRRRRR GGGGGGGG BBBBBBBB
     *
     * The returned tuple contains the components in the same order (alpha, red, green, blue). Use array destructuring
     * to extract a colour:
     *
     * [$alpha, $red, $green, $blue] = Colour::toRgba($colourInt);
     *
     * @param int $colour The colour to unpack.
     *
     * @return array<int> A tuple of four ints representing the ARGB colour.
     */
    public static function toRgba(int $colour): array
    {
        return [
            ($colour & 0xff000000) >> 24,
            ($colour & 0xff0000) >> 16,
            ($colour & 0xff00) >> 8,
            ($colour & 0xff),
        ];
    }
}
