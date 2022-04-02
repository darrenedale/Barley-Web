<?php

namespace App\Providers;

use App\Exceptions\ConfigurationException;
use App\Generators\GeneratorFactoryInterface;
use App\Generators\GeneratorFactory;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // bind the implementation of the GeneratorFactoryInterface for the app
        $this->app->bind(GeneratorFactoryInterface::class, function() {
            // do a late binding only when the instance is first required
            static $factory = null;

            if (!$factory) {
                // the implementation can be set in the barcode.php config file, or the default will be used if no
                // implementation is specified
                $factoryClass = config("barcode.generator-factory", GeneratorFactory::class);

                if (!is_subclass_of($factoryClass, GeneratorFactoryInterface::class, true)) {
                    throw new ConfigurationException("barcode.php", "generator-factory", "The configured barcode generator factory does not implement the " . GeneratorFactoryInterface::class . " interface");
                }

                try {
                    $factory = new $factoryClass();
                } catch (Throwable $e) {
                    throw new ConfigurationException("barcode.php", "generator-factory", "The configured barcode generator factory threw an exception when instantiated.", 0, $e);
                }
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
