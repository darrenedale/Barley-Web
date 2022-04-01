<?php

namespace App\Console\Commands;

use App\Models\Barcode;

/**
 * Command to prune soft-deleted records from the barcodes table.
 *
 * Barcodes that were soft-deleted more than 7 days ago will be pruned.
 */
class PruneBarcodes extends PruneCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prune:barley-barcodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune all barcodes records that were soft-deleted over 7 days ago';

    /**
     * @var int|null The cutoff age, in days, for pruning soft-deletes.
     */
    protected ?int $age = 7;

    /**
     * @var string|null The model class for barcodes.
     */
    protected ?string $model = Barcode::class;
}
