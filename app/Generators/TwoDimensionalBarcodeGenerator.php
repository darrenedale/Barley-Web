<?php

namespace App\Generators;

use App\Util\Size;

/**
 * Abstract base class for two-dimensional bar codes.
 *
 * It provides a base implementation of closestIdealSizeTo() which ensures that the size's width and height are integer
 * multiples of the minimum width and height respectively.
 */
abstract class TwoDimensionalBarcodeGenerator extends BarcodeGenerator
{
    /**
     * Fetch the nearest optimal size for a given client-preferred size.
     *
     * Some barcodes work better at a specific set of dimensions. Some might have a minimum or maximum size. This method
     * provides clients with an opportunity to optimise the size they request before generating the bitmap. Clients are
     * free to discard the optimised size and stick with their preferred size. Except in cases where there is a minimum
     * size, generators should stick to the sizes requested by clients wherever possible.
     */
    public function closestIdealSizeTo(Size $size): Size
    {
        // linear barcode ideal size - width is an integer multiple of the minimum size; height is
        // scaled in proportion
        $idealSize = $this->minimumSize();

        if ($idealSize->equals($size)) {
            return $size;
        }

        if ($size->width % $size->width > $idealSize->width / 2) {
            // if requested width is over half way to the next multiple of the minimum, round up
            $idealSize->width *= ceil($size->width / $idealSize->width);
        } else {
            // otherwise, round down to previous multiple of minimum width
            $idealSize->width *= floor(max(1, $size->width) / $idealSize->width);
        }

        if ($size->height % $size->height > $idealSize->height / 2) {
            // if requested height is over half way to the next multiple of the minimum, round up
            $idealSize->height *= ceil($size->height / $idealSize->height);
        } else {
            // otherwise, round down to previous multiple of minimum width
            $idealSize->height *= floor(max(1, $size->height) / $idealSize->height);
        }

        return $idealSize;
    }
}
