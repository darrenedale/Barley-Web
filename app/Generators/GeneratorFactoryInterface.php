<?php

namespace App\Generators;

interface GeneratorFactoryInterface
{
    public function hasGeneratorFor(string $type): bool;
    public function generatorClass(string $type): ?string;
    public function generatorFor(string $type): ?BarcodeGenerator;
    public function setGenerator(string $type, string $generatorClass): void;
    public function generate(string $type): BarcodeGenerator;
}
