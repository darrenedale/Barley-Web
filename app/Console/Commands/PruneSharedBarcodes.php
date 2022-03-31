<?php

namespace App\Console\Commands;

use App\Models\SharedBarcode;

/**
 * Command to prune old soft-deleted records from the shared_barcodes table.
 *
 * Shared barcodes that were soft-deleted more than 7 days ago are pruned.
 */
class PruneSharedBarcodes extends PruneCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prune:barley-shared-barcodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var string|null The model class for shared barcodes.
     */
    protected ?string $model = SharedBarcode::class;

    /**
     * @var int|null The cutoff age, in days, for pruning soft-deletes.
     */
    protected ?int $age = 7;
}
