<?php

namespace App\Providers;

use App\Generators\GeneratorFactoryInterface;
use App\Generators\GeneratorFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(GeneratorFactoryInterface::class, function() {
            static $factory = null;

            if (!$factory) {
                $factory = new GeneratorFactory();
            }

            return $factory;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
