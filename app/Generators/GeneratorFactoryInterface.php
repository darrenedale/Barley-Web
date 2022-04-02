<?php

namespace App\Generators;

/**
 * Interface for factories for BarcodeGenerator instances.
 */
interface GeneratorFactoryInterface
{
    /**
     * Check whether the factory can provide a generator for a given barcode type.
     *
     * @param string $type The barcode type identifier.
     *
     * @return bool
     */
    public function hasGeneratorFor(string $type): bool;

    /**
     * Fetch the fully-qualified class name of the generator for a given barcode type.
     *
     * @param string $type The barcode type identifier.
     *
     * @return string|null The class name, or null if the type has no generator available.
     */
    public function generatorClass(string $type): ?string;

    /**
     * Create an instance of the generator for a given barcode type.
     *
     * @param string $type The barcode type identifier.
     *
     * @return BarcodeGenerator|null A generator instance, or null if the type has no generator available.
     */
    public function generatorFor(string $type): ?BarcodeGenerator;

    /**
     * Set the fully-qualified class name of the generator for a given barcode type.
     *
     * @param string $type The barcode type identifier.
     * @param string $generatorClass The name of the generator class.
     *
     * @throws \InvalidArgumentException if the provided class is not a subclass of BarcodeGenerator.
     */
    public function setGenerator(string $type, string $generatorClass): void;

    /**
     * Create an instance of the generator for a given barcode type.
     *
     * Effectively this is an alias of generatorFor(), but with a syntax that is more expressive when used with the
     * Facade. It also throws if a generator is not available, rather than returning null.
     *
     * @param string $type The barcode type identifier.
     *
     * @return BarcodeGenerator A generator instance, or null if the type has no generator available.
     * @throws \App\Exceptions\BarcodeGeneratorNotFoundException if no generator is available for the type.
     */
    public function generate(string $type): BarcodeGenerator;
}
