<?php

namespace App\Generators;

use App\Exceptions\BarcodeGeneratorNotFoundException;
use FilesystemIterator;
use Illuminate\Support\Facades\Log;

class GeneratorFactory
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
     * @param string $type The barcode type identifier.
     * @param string $generatorClass The fully-qualified class name of the generator for the type.
     */
    public function setGenerator(string $type, string $generatorClass): void
    {
        $this->ensureGeneratorsDiscovered();
        $this->m_generators[$type] = $generatorClass;
    }

    /**
     * Fetch the class name of the generator for a given type, if it is available.
     *
     * @param string $type The type.
     *
     * @return string|null The generator class name (including namespace), or null if no genrator is available for the
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
        return isset($this->m_generators[$type]);
    }

    /**
     * Must not be called before m_generators has been initialised.
     *
     * @param \SplFileInfo $file The file to load.
     * @param string $namespace The namespace the generator class is expected to be in.
     *
     * @return bool Whether or not loading a generator class from the provided file succeeded.
     */
    public function loadGeneratorClass(\SplFileInfo $file, string $namespace, bool $override = false): bool
    {
        $className = "{$namespace}\\{$file->getBasename()}";
        include_once($file->getPathname());

        if (!class_exists($className)) {
            Log::debug("File '{$file->getPathname()}' does not define a class '{$className}'.");
            return false;
        }

        if (!is_a($className, BarcodeGenerator::class)) {
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
     * Must not be called before m_generators has been initialised.
     *
     * @param string $path
     * @param string $namespace
     *
     * @return void
     */
    protected function discoverGeneratorsForPath(string $path, string $namespace): void
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
                Log::error("exception loading Generator class {$namespace}\\{$file->getBasename()} from {$file->getPathname()}: {$e->getMessage()}.");
            }
        }
    }

    /**
     * Helper to load generators from the set paths.
     *
     * @return void
     */
    protected function discoverGenerators(): void
    {
        $this->m_generators = [];

        foreach ($this->searchPaths() as $path => $namespace) {
            $this->discoverGeneratorsForPath($path, $namespace);
        }
    }

    /**
     * Get a generator for a given type of barcode.
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
