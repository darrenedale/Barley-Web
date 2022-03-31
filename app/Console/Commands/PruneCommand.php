<?php

namespace App\Console\Commands;

use App\Exceptions\InvalidPruneModelException;
use App\Exceptions\MissingPruneModelException;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Abstract base class for commands that prune soft-deleted records from the database.
 */
abstract class PruneCommand extends Command
{
    /**
     * Return code indicating that the age specified as the threshold for pruning is not valid.
     */
    public const ErrInvalidAge = 1;

    /**
     * Return code indicating that the model class specified for pruning is not valid.
     */
    public const ErrInvalidModel = 2;

    /**
     * @var string|null Set this to the class name of the Eloquent model that is being pruned.
     *
     * Setting this to a valid model class will cause the default implementation of getQueryBuilder() to call query() on
     * that model to get the base query to select the models to be pruned (an age constraint for deleted_at is added
     * according to the returned value from getAge() in handle()). If you need finer control over the base query,
     * reimplement getQueryBuilder() in your subclass.
     */
    protected ?string $model;

    /**
     * @var int|null The number of days of soft-deleted models to retain.
     *
     * Anything soft-deleted more than this many days ago will be deleted. If you need more control over the age than
     * a fixed property value, don't set this property and reimplement getAge() instead.
     */
    protected ?int $age;

    /**
     * The age, in days, for which to retain soft-deleted models.
     *
     * Anything soft-deleted before this age that is selected by the base query will be pruned.
     *
     * @return int
     */
    public function getAge(): int
    {
        if (is_null($this->age)) {
            throw new \RuntimeException("Base implementation of getAge() called with no defined age property.");
        }

        return $this->age;
    }

    /**
     * Helper to get the base query for pruning.
     *
     * This is the query before the deleted_at constraint is added.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \App\Exceptions\InvalidPruneModelException
     * @throws \App\Exceptions\MissingPruneModelException
     */
    protected function getQueryBuilder(): Builder
    {
        if (!$this->model) {
            throw new MissingPruneModelException("Base implementation of getQueryBuilder() called with no defined model class.");
        }

        if (!is_subclass_of($this->model, Model::class)) {
            throw new InvalidPruneModelException($this->model, "Defined model class is not a subclass of " . Model::class . ".");
        }

        return call_user_func([$this->model, "query"]);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        try {
            $age = $this->getAge();
            $res = $this->getQueryBuilder()
                ->where("deleted_at", "<", new DateTime("{$age} days ago"))
                ->forceDelete();
        } catch (MissingPruneModelException $e) {
            $this->error("Missing model class when pruning in command " . static::class . ".");
            return self::ErrInvalidModel;
        } catch (InvalidPruneModelException $e) {
            $this->error("Invalid model class '{$e->getModel()}' when pruning in command " . static::class . ".");
            return self::ErrInvalidModel;
        } catch (Exception) {
            $this->error("The age {$age} did not produce a valid cutoff date.");
            return self::ErrInvalidAge;
        }

        $this->line("pruned {$res} record(s).");
        return self::ExitOk;
    }

}
