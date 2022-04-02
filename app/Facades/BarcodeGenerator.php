<?php

namespace App\Facades;

use App\Generators\GeneratorFactoryInterface;
use Illuminate\Support\Facades\Facade;

/**
 * Facade for accessing barcode generators.
 *
 * The facade uses the concrete class bound to the GeneratorFactoryInterface in the app container.
 */
class BarcodeGenerator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GeneratorFactoryInterface::class;
    }
}
