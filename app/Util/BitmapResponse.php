<?php

namespace App\Util;

use Illuminate\Http\Response;

/**
 * Use a generated bitmap directly as a Laravel response.
 *
 * The bitmap output format can be chosen from one of the BitmapOutputFormat enumerators. The default is
 * BitmapOutputFormat::Png.
 */
class BitmapResponse extends Response
{
    public function __construct(Bitmap $bitmap, BitmapOutputFormat $format = BitmapOutputFormat::Png)
    {
        parent::__construct($bitmap->data($format), 200, ["content-type" => match($format) {
            BitmapOutputFormat::Png => "image/png",
            BitmapOutputFormat::Jpeg => "image/jpeg",
            BitmapOutputFormat::Bmp => "image/bmp",
            BitmapOutputFormat::Gif => "image/gif",
            BitmapOutputFormat::Webp => "image/webp",
        }]);
    }
}
