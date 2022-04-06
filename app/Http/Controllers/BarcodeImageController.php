<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidDataException;
use App\Exceptions\InvalidDimensionException;
use App\Facades\BarcodeGenerator;
use App\Util\BitmapOutputFormat;
use App\Util\BitmapResponse;
use App\Util\Size;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;

class BarcodeImageController extends Controller
{
    public const PlaceholderImagePath = "images/barcode-placeholder.svg";
    public const DefaultImageWidth = 500;
    public const DefaultImageAspectRatio = 2;
    public const DefaultImageFormat = BitmapOutputFormat::Png;

    /**
     * Send the placeholder barcode image.
     *
     * This is implemented as a redirect to the URL of the placeholder image.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function placeholderBarcodeImage(): RedirectResponse
    {
        return redirect()->to(self::PlaceholderImagePath);
    }

    /**
     * Generate a barcode image.
     *
     * If the data is null, it is assumed to be in the request's POST data. The data sent in the response is image data
     * not HTML. The image format depends on the format specified in the $format argument.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $type
     * @param string|null $data
     * @param string|\App\Util\BitmapOutputFormat $format
     * @param int $width
     * @param int|null $height
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function barcodeImage(Request $request, string $type, ?string $data = null, string | BitmapOutputFormat $format = self::DefaultImageFormat, int $width = self::DefaultImageWidth, int $height = null): Response | RedirectResponse
    {
        if (!BarcodeGenerator::hasGeneratorFor($type)) {
            return $this->placeholderBarcodeImage();
        }

        if(is_string($format)) {
            $format = match ($format) {
                "png" => BitmapOutputFormat::Png,
                "jpeg" => BitmapOutputFormat::Jpeg,
                "gif" => BitmapOutputFormat::Gif,
                "webp" => BitmapOutputFormat::Webp,
                "bmp" => BitmapOutputFormat::Bmp,
                default => throw new InvalidArgumentException("Unrecognised image format {$format}."),
            };
        }

        if (!isset($data)) {
            $data = $request->post("data");

            if (!isset($data)) {
                return $this->placeholderBarcodeImage();
            }
        }

        if (!isset($height)) {
            $height = $width / self::DefaultImageAspectRatio;
        }

        try {
            /** @var \App\Generators\BarcodeGenerator $generator */
            $generator = BarcodeGenerator::generate($type);
            return new BitmapResponse($generator
                ->withData($data)
                ->atSize(new Size($width, $height))
                ->getBitmap(), $format);
        } catch (InvalidDimensionException) {
            // invalid image size
            return $this->placeholderBarcodeImage();
        } catch (InvalidDataException) {
            // barcode type can't encode provided data
            return $this->placeholderBarcodeImage();
        }
    }
}
