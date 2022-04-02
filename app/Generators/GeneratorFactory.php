<?php

namespace App\Generators;

use App\Exceptions\BarcodeGeneratorNotFoundException;
use FilesystemIterator;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Factory to produce BarcodeGenerator instances based on their identifier strings.
 *
 * The factory is able to auto-load from given paths and discover any BarcodeGenerator subclasses therein. Currently it
 * only looks inside its own directory.
 */
class GeneratorFactory implements GeneratorFactoryInterface
{
    /**
     * Paths to search for generators. Key is the fs path, value is the namespace for generator classes in that path.
     */
    private array $m_searchPaths;

    /**
     * @var array|null Map of barcode types to generator classes that provide them.
     */
    private ?array $m_generators = null;

    /**
     * Initialise a new factory.
     */
    public function __construct()
    {
        $this->m_searchPaths = [
            dirname(__FILE__) => "App\\Generators",
        ];
    }

    /**
     * The paths in which to discover barcode generator classes.
     *
     * Keys are the filesystem paths, values are the namespaces that the classes in those paths are expected to be in.
     *
     * @return string[]
     */
    protected function searchPaths(): array
    {
        return $this->m_searchPaths;
    }

    /**
     * Check whether the barcode generators in the configured paths have been discovered.
     *
     * This also returns true if the generators are in the process of being discovered.
     *
     * @return bool true if they have, false otherwise.
     */
    protected function generatorsDiscovered(): bool
    {
        return null !== $this->m_generators;
    }

    /**
     * Helper to ensure generators in the configured paths have been discovered.
     *
     * If the generators have already been discovered, this is a no-op.
     */
    protected function ensureGeneratorsDiscovered(): void
    {
        if (!$this->generatorsDiscovered()) {
            $this->discoverGenerators();
        }
    }

    /**
     * Set the generator class to use for a given type.
     *
     * The provided generator will be used for the provided type. If a generator for the type is already set, it will
     * be replaced. The provided class name must identify a subclass of BarcodeGenerator
     *
     * @param string $type The barcode type identifier.
     * @param string $generatorClass The fully-qualified class name of the generator for the type.
     *
     * @throws InvalidArgumentException if the provided class is not a BarcodeGenerator subclass.
     */
    public function setGenerator(string $type, string $generatorClass): void
    {
        $this->ensureGeneratorsDiscovered();

        if (!is_subclass_of($generatorClass, BarcodeGenerator::class, true)) {
            throw new InvalidArgumentException("Class '{$generatorClass}' from is not a BarcodeGenerator subclass");
        }

        $this->m_generators[$type] = $generatorClass;
    }

    /**
     * Fetch the class name of the generator for a given type, if it is available.
     *
     * @param string $type The type.
     *
     * @return string|null The generator class name (including namespace), or null if no generator is available for the
     * barcode type.
     */
    public function generatorClass(string $type): ?string
    {
        $this->ensureGeneratorsDiscovered();
        return $this->m_generators[$type] ?? null;
    }

    /**
     * Fetch a new generator instance for a given barcode type.
     *
     * @param string $type The barcode type for which a generator is requested.
     *
     * @return \App\Generators\BarcodeGenerator|null
     */
    public function generatorFor(string $type): ?BarcodeGenerator
    {
        $this->ensureGeneratorsDiscovered();
        return isset($this->m_generators[$type]) ? new $this->m_generators[$type] : null;
    }

    /**
     * Check whether a generator is available for a given barcode type.
     *
     * @param string $type The type for which to query whether a generator is available.
     *
     * @return bool Whether a generator for the provided type is available.
     */
    public function hasGeneratorFor(string $type): bool
    {
        $this->ensureGeneratorsDiscovered();
        return isset($this->m_generators[$type]);
    }

    /**
     * Helper to attempt to load a BarcodeGenerator subclass from a file.
     *
     * This must not be called before m_generators has been initialised.
     *
     * @param \SplFileInfo $file The file to load.
     * @param string $namespace The namespace the generator class is expected to be in.
     * @param bool $override Whether the loaded class should override any previous BarcodeGenerator class that lays
     * claim to the same identifier.
     *
     * @return bool Whether loading a generator class from the provided file succeeded. When the file contains a
     * BarcodeGenerator but it is not used because the type identifier it provides is already in use and $override is
     * false, the return value is false.
     */
    public function loadGeneratorClass(\SplFileInfo $file, string $namespace, bool $override = false): bool
    {
        $className = "{$namespace}\\{$file->getBasename(".php")}";
        include_once($file->getPathname());

        if (!class_exists($className)) {
            Log::debug("File '{$file->getPathname()}' does not define a class '{$className}'.");
            return false;
        }

        if (!is_subclass_of($className, BarcodeGenerator::class, true)) {
            Log::debug("Discovered class '{$className}' from '{$file->getPathname()}' is not a BarcodeGenerator");
            return false;
        }

        $type = call_user_func([$className, "typeIdentifier",]);

        if (!$override && array_key_exists($type, $this->m_generators)) {
            Log::debug("Discovered class '{$className}' from '{$file->getPathname()}' generates barcodes of type '{$type}', which is already provided by '{$this->m_generators[$type]}'");
            return false;
        }

        $this->setGenerator($type, $className);
        return true;
    }

    /**
     * Helper to scan a given path for BarcodeGenerator classes.
     *
     * This must not be called before the m_generators member has been initialised.
     *
     * @param string $path The path to scan for BarcodeGenerator classes.
     * @param string $namespace The namespace the classes are expected to be in.
     */
    protected function discoverGeneratorsInPath(string $path, string $namespace): void
    {
        /** @var \SplFileInfo $file */
        foreach (new FilesystemIterator($path) as $file) {
            if ("php" !== $file->getExtension()) {
                continue;
            }

            try {
                if (!$this->loadGeneratorClass($file, $namespace)) {
                    Log::error("file {$file->getPathname()} did not contain a BarcodeGenerator in the {$namespace} namespace.");
                }
            } catch (\Throwable $e) {
                Log::error("exception loading Generator class {$namespace}\\{$file->getBasename(".php")} from {$file->getPathname()}: {$e->getMessage()}.");
            }
        }
    }

    /**
     * Helper to load generators from the set paths.
     */
    protected function discoverGenerators(): void
    {
        $this->m_generators = [];

        foreach ($this->searchPaths() as $path => $namespace) {
            $this->discoverGeneratorsInPath($path, $namespace);
        }
    }

    /**
     * Get a generator for a given type of barcode.
     *
     * This method is intended for use with the facade, for example:
     *
     *     $bmp = BarcodeGenerator::generate("code128")->widthData("fizzbuzz")->atSize(new Size(500, 250))->getBitmap();
     *
     * @param string $type The barcode type for which a generator is required.
     *
     * @return \App\Generators\BarcodeGenerator
     * @throws \App\Exceptions\BarcodeGeneratorNotFoundException
     */
    public function generate(string $type): BarcodeGenerator
    {
        if (!$this->hasGeneratorFor($type)) {
            throw new BarcodeGeneratorNotFoundException($type);
        }

        return $this->generatorFor($type);
    }
}
