<?php

use App\Http\Controllers\BarcodeImageController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// authentication
Route::post("/login", [LoginController::class, "login",]);
Route::get("/logout", [LoginController::class, "logout",]);
Route::get("/login/2fa", [LoginController::class, "showTwoFactorLoginForm",])
    ->middleware("auth")
    ->name("2fa.login");
Route::post("/login/2fa", [LoginController::class, "secondFactorLogin",])
    ->middleware("auth");

// home page
Route::get("/", function () {
    return view("home");
});

// dynamic barcode image generation
Route::get("/barcode-image/{type}/{data}/{format?}/{width?}/{height?}", [BarcodeImageController::class, "barcodeImage"])
    ->where(["width" => "[1-9][0-9]*", "height" => "[1-9][0-9]*", ]);

Route::post("/barcode-image/{type}/{format?}/{width?}/{height?}", [BarcodeImageController::class, "barcodeImage"])
    ->where(["width" => "[1-9][0-9]*", "height" => "[1-9][0-9]*", ]);
