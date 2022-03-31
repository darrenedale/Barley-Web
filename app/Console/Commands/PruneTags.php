<?php

namespace App\Console\Commands;

use App\Models\Tag;

/**
 * Command to prune old soft-deleted records from the tags table.
 *
 * Tags that were soft-deleted more than 7 days ago are pruned.
 */
class PruneTags extends PruneCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prune:barley-tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var string|null The model class for tags.
     */
    protected ?string $model = Tag::class;

    /**
     * @var int|null The cutoff age, in days, for pruning soft-deletes.
     */
    protected ?int $age = 7;
}
