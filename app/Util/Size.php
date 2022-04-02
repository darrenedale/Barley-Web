<?php

namespace App\Util;

/**
 * Lightweight two-dimensional size.
 */
class Size
{
    public const DefaultWidth = 0;
    public const DefaultHeight = 0;

    /**
     * @var int The width of the size.
     */
    public int $width;

    /**
     * @var int The height of the size.
     */
    public int $height;

    public function __construct(Size | int $sizeOrWidth = null, int $height = null)
    {
        if ($sizeOrWidth instanceof Size) {
            $this->initialiseFromSize($sizeOrWidth);
        } else if (is_int($sizeOrWidth) && is_int($height)) {
            $this->initialiseFromDimensions($sizeOrWidth, $height);
        } else if(is_null($sizeOrWidth) && is_null($height)) {
            $this->width = self::DefaultWidth;
            $this->height = self::DefaultHeight;
        } else {
            throw new \InvalidArgumentException("Size must be initialised with either another Size instance or integer dimensions");
        }
    }

    /**
     * Helper to initialise a new Size object from integer dimensions.
     *
     * @param int $width
     * @param int $height
     */
    protected final function initialiseFromDimensions(int $width, int $height): void
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Helper to initialise a new Size object from another instance.
     *
     * @param Size $size The size to use to initialise this.
     */
    protected final function initialiseFromSize(Size $size): void
    {
        $this->width = $size->width;
        $this->height = $size->height;
    }

    /**
     * Fetch a string representation of the Size instance.
     *
     * @return string
     */
    public function __toString(): string
    {
        return "Size[{$this->width} x {$this->height}]";
    }

    /**
     * Check whether the size is equal to another.
     *
     * @param Size $other The size to compare to.
     *
     * @return bool true if the two sizes have identical widths and heights, false otherwise.
     */
    public function equals(Size $other): bool
    {
        return $this->height === $other->height && $this->width === $other->width;
    }
}
