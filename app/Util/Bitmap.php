<?php

namespace App\Util;

/**
 * Transitional class to aid the port of barcode generators from the Android app.
 */
class Bitmap
{
    public function __constructor(Size | int $widthOrSize, int $height = null)
    {
        if ($widthOrSize instanceof Size) {
            $this->initialiseFromSize($widthOrSize);
        } else {
            if (is_null($height)) {
                throw new \InvalidArgumentException("Bitmap objects must be created using a Size object or integer dimensions.");
            }

            $this->initialiseFromDimensions($widthOrSize, $height);
        }
    }

    protected function initialiseFromSize(Size $size): void
    {
        $this->m_image = imagecreatetruecolor($size->width, $size->height);
    }

    protected function initialiseFromDimensions(int $width, int $height): void
    {
        $this->m_image = imagecreatetruecolor($width, $height);
    }

    public function setPixel(int $x, int $y, int $colour): void
    {
        imagesetpixel($this->m_image, $x, $y, $colour);
    }

    public const FormatPng = 0;
    public const FormatJpeg = 1;

    public function data(int $format = self::FormatPng): string
    {
        ob_start();

        match($format) {
            self::FormatPng => imagepng($this->m_image),
            self::FormatJpeg => imagejpeg($this->m_image),
        };

        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    public static function createBitmap(Size | int $sizeOrWidth, ?int $height = null): Bitmap
    {
        return new Bitmap($sizeOrWidth, $height);
    }

    public static function createScaledBitmap(Bitmap $bitmap, int $width, int $height, bool $bilinearFilter = false): Bitmap
    {
        $ret = new Bitmap(1, 1);
        $ret->m_image = imagescale($bitmap->m_image, $width, $height, ($bilinearFilter ? IMG_BILINEAR_FIXED : IMG_NEAREST_NEIGHBOUR));
        return $ret;
    }

    private \GdImage $m_image;
}
