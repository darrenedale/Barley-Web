<?php

use App\Facades\GeneratorFactory;
use Illuminate\Support\Facades\Route;

Route::prefix("dev")->group(function() {
    Route::get("/sample-barcode/{type}/{data}", function(string $type, string $data) {
        abort_if(!GeneratorFactory::hasGeneratorFor($type), 404, "Barcode type {$type} is not valid or not supported.");
        return GeneratorFactory::generate($type)->setData($data)->getBitmap();
    });
});
