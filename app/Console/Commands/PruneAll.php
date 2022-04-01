<?php

namespace App\Console\Commands;

/**
 * Command to prune all soft-deleted data from the database.
 *
 * This aggregates all the barley prune:barley-* commands. Each individual command has its own threshold for deciding
 * how long ago a soft-delete occurred for it to qualify for pruning.
 */
class PruneAll extends AggregateCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prune:barley-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = <<<'EOT'
Prune all database records that were soft-deleted over a threshold number of days ago. (The threshold differs according
to the type of record being pruned - see individual prune:barley-* commands for details.)
EOT;

    /**
     * The individual commands that do the pruning of specific models.
     *
     * @var array|string[]
     */
    protected array $commands = [
        "prune:barley-users",
        "prune:barley-barcodes",
        "prune:barley-tags",
        "prune:barley-shared-barcodes",
    ];

    protected bool $abortOnFail = false;
}
