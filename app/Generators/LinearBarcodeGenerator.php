<?php

namespace App\Generators;

use App\Util\Bitmap;
use App\Util\Size;
use App\Util\Colour as Color;

abstract class LinearBarcodeGenerator extends BarcodeGenerator
{
    /**
     * Fetch the nearest optimal size for a given client-preferred size.
     *
     * Some barcodes work better at a specific set of dimensions. Some might have a minimum or
     * maximum size. This method provides clients with an opportunity to optimise the size they
     * request before generating the bitmap. Clients are free to discard the optimised size and
     * stick with their preferred size. Except in cases where there is a minimum size, generators
     * should stick to the sizes requested by clients wherever possible.
     *
     * NOTE current base implementation assumes all barcode generators produce horizontal linear
     * barcodes.
     */
    public function closestIdealSizeTo(Size $size): Size
    {
        // linear barcode ideal size - width is an integer multiple of the minimum size; height is
        // scaled in proportion
        $width = $this->minimumSize()->width;

        if ($size->width == $width) {
            // requested size is ideal
            return $size;
        }

        if ($size->width % $width > $width / 2) {
            // if requested width is over half way to the next multiple of the minimum, round up
            $width *= ceil($size->width / $width);
        } else {
            // otherwise, round down to previous multiple of minimum width
            $width *= floor($size->width / $width);
        }

        return new Size($width, (int)((double)$size->height * $width / $size->width));
    }

    /**
     * Helper to render a pattern to the bitmap for a barcode.
     *
     * The helper assumes that the barcode is being rendered with a single pixel representing each bit in the provided
     * pattern. The pattern is rendered at x = offset and y = 0 in the bitmap. 0s in the pattern are rendered white, 1s
     * black. Only the rightmost bits are rendered.
     *
     * @param \App\Util\Bitmap $bmp The bitmap to which to render.
     * @param int $pattern The bit pattern to render.
     * @param int $bits The rightmost number of bits in the pattern to render.
     * @param int $offset The horizontal offset into the bitmap at which to render the pattern.
     */
    protected static function renderPatternToBitmap(Bitmap $bmp, int $pattern, int $bits, int $offset): void
    {
        $mask = 1 << ($bits - 1);

        for ($bit = 0; $bit < $bits; ++$bit) {
            $bmp->setPixel($offset, 0, (0 == ($pattern & $mask)) ? Color::WHITE : Color::BLACK);
            ++$offset;
            $mask >>= 1;
        }
    }
}
