<?php

namespace App\Providers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
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
                Route::get("/send-sample-mail/{to?}", function (Request $request, string $to = "darren.edale@protonmail.com") {
                    $mail = new class extends Mailable
                    {
                        public function build()
                        {
                            return $this->text("dev.sample-mail");
                        }
                    };

                    Mail::to($to)->send($mail);
                });
            });
    }
}
