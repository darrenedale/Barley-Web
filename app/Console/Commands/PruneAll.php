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
    protected $description = 'Command description';

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
