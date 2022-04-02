<?php

namespace App\Util;

use App\Exceptions\InvalidDimensionException;

/**
 * Transitional class to aid the port of barcode generators from the Android app.
 */
class Bitmap
{
    /**
     * Initialise a new bitmap, either with a Size or with integer dimensions.
     *
     * The bitmap is always a true-colour bitmap of ARGB pixels, with 8 bits per channel.
     *
     * @param \App\Util\Size|int $widthOrSize The Size, or the width if providing individual dimensions.
     * @param int|null $height The height, or null if providing a Size.
     *
     * @throws \App\Exceptions\InvalidDimensionException
     */
    public function __construct(Size | int $widthOrSize, int $height = null)
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

    /**
     * Helper for the constructor to initialise a new Bitmap from a Size object.
     *
     * @param \App\Util\Size $size
     */
    protected function initialiseFromSize(Size $size): void
    {
        $this->m_image = imagecreatetruecolor($size->width, $size->height);
    }

    /**
     * Helper for the constructor to initialise a new Bitmap from individual dimensions.
     *
     * Both dimensions must be positive integers.
     *
     * @param int $width The bitmap's width.
     * @param int $height The bitmap's height.
     *
     * @throws \App\Exceptions\InvalidDimensionException if either dimension is < 1.
     */
    protected function initialiseFromDimensions(int $width, int $height): void
    {
        if (0 >= $width) {
            throw new InvalidDimensionException($width, "width", "The width of a bitmap must be a positive integer.");
        }

        if (0 >= $height) {
            throw new InvalidDimensionException($height, "height", "The height of a bitmap must be a positive integer.");
        }

        $this->m_image = imagecreatetruecolor($width, $height);
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $colour
     *
     * @return void
     */
    public function setPixel(int $x, int $y, int $colour): void
    {
        $colour = imagecolorallocate($this->m_image, ($colour & 0xff0000) >> 16, ($colour & 0x00ff00) >> 8, $colour & 0x0000ff);
        imagesetpixel($this->m_image, $x, $y, $colour);
    }

    /**
     * Check whether a given bitmap output format can be produced.
     *
     * @param \App\Util\BitmapOutputFormat $format
     *
     * @return bool
     */
    public static function isFormatSupported(BitmapOutputFormat $format): bool
    {
        return 0 != (imagetypes() & match($format) {
            BitmapOutputFormat::Png => IMG_PNG,
            BitmapOutputFormat::Jpeg => IMG_JPEG,
            BitmapOutputFormat::Gif => IMG_GIF,
            BitmapOutputFormat::Bmp => IMG_BMP,
            BitmapOutputFormat::Webp => IMG_WEBP,
        });
    }

    /**
     * Get the image data for the Bitmap in a specified format.
     *
     * @param \App\Util\BitmapOutputFormat $format The format required.
     *
     * @return string The bytes representing the bitmap's content in the specified format.
     */
    public function data(BitmapOutputFormat $format = BitmapOutputFormat::Png): string
    {
        ob_start();

        match($format) {
            BitmapOutputFormat::Png => imagepng($this->m_image),
            BitmapOutputFormat::Jpeg => imagejpeg($this->m_image),
            BitmapOutputFormat::Bmp => imagebmp($this->m_image),
            BitmapOutputFormat::Gif => imagegif($this->m_image),
            BitmapOutputFormat::Webp => imagewebp($this->m_image),
        };

        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    /**
     * Transitional method for creating a bitmap of a given size.
     *
     * @param \App\Util\Size|int $sizeOrWidth
     * @param int|null $height
     *
     * @return \App\Util\Bitmap
     * @throws \App\Exceptions\InvalidDimensionException
     */
    public static function createBitmap(Size | int $sizeOrWidth, ?int $height = null): Bitmap
    {
        return new Bitmap($sizeOrWidth, $height);
    }

    /**
     * Transitional method for creating a bitmap of a given size that is a scaled duplicate of another.
     *
     * @param \App\Util\Bitmap $bitmap The bitmap to copy and scale.
     * @param int $width The new width for the scaled duplicate.
     * @param int $height The new height for the scaled duplicate.
     * @param bool $bilinearFilter Whether to use the bilinear filter when scaling.
     *
     * @return \App\Util\Bitmap
     * @throws \App\Exceptions\InvalidDimensionException
     */
    public static function createScaledBitmap(Bitmap $bitmap, int $width, int $height, bool $bilinearFilter = false): Bitmap
    {
        $ret = new Bitmap(1, 1);
        $ret->m_image = imagescale($bitmap->m_image, $width, $height, ($bilinearFilter ? IMG_BILINEAR_FIXED : IMG_NEAREST_NEIGHBOUR));
        return $ret;
    }

    /**
     * @var \GdImage The underlying GD image instance.
     */
    private \GdImage $m_image;
}
