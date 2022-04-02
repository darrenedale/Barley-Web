<?php

namespace App\Util;

/**
 * Enumeration of possible output formats for Bitmap objects.
 *
 * Bitmap implementations need not support all formats, but should try to support as many as possible. As a bare
 * minimum, PNG and JPEG are recommended.
 */
enum BitmapOutputFormat
{
    case Png;
    case Jpeg;
    case Gif;
    case Bmp;
    case Webp;
}
