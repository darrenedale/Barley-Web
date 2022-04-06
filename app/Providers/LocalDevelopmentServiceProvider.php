<?php

namespace App\Providers;

use App\Facades\BarcodeGenerator;
use App\Util\Bitmap;
use App\Util\BitmapOutputFormat;
use App\Util\BitmapResponse;
use App\Util\Size;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for local development only.
 *
 * Use this to add your own routes, etc. during development. Remove your changes to this file before creating your pull
 * request - it should always remain a "bare bones" service provider in main.
 */
class LocalDevelopmentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // guard against using this service provider in test or production
        if ("local" !== config("app.env")) {
            return;
        }

        Route::middleware("web")
            ->prefix("dev")
            ->group(function () {
                // put your development routes here while you work. remove them before you create your PR
                Route::get("/create-barcode/{type}/{data}/{format?}/{width?}/{height?}", function (string $type, string $data, string $format = "png", ?int $width = null, ?int $height = null) {
                    abort_if(!BarcodeGenerator::hasGeneratorFor($type), 404, "Barcode type {$type} is not valid or not supported.");

                    $format = match (strtolower($format)) {
                        "png" => BitmapOutputFormat::Png,
                        "jpeg" => BitmapOutputFormat::Jpeg,
                        "bmp" => BitmapOutputFormat::Bmp,
                        "gif" => BitmapOutputFormat::Gif,
                        "webp" => BitmapOutputFormat::Webp,
                    };

                    abort_if(!Bitmap::isFormatSupported($format), 404, "Image format {$format->name} is not supported.");

                    if (!isset($width)) {
                        $width = 500;
                        $height = 250;
                    } else if (!isset($height)) {
                        $height = 0.5 * $width;
                    }

                    return new BitmapResponse(
                        BarcodeGenerator::generate($type)
                            ->withData($data)
                            ->atSize(new Size($width, $height))
                            ->getBitmap(),
                        $format
                    );
                })->where(["width" => "[1-9][0-9]*", "height" => "[1-9][0-9]*",]);
            });
    }
}
