<?php

namespace App\Console\Commands;

use App\Models\User;

/**
 * Command to prune soft-deleted users from the database.
 *
 * Users that were soft-deleted more than 28 days ago are pruned.
 */
class PruneUsers extends PruneCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "prune:barley-users";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command description";

    /**
     * @var string|null Prune User models.
     */
    protected ?string $model = User::class;

    /**
     * @var int|null Prune models soft-deleted more than 28 days ago.
     */
    protected ?int $age = 28;
}
