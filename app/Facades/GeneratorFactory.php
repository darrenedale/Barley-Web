<?php

namespace App\Facades;

use App\Generators\GeneratorFactoryInterface;
use Illuminate\Support\Facades\Facade;

class GeneratorFactory extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GeneratorFactory::class;
    }
}
