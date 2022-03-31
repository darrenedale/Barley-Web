<?php

namespace App\Console\Commands;

use App\Exceptions\AggregatedCommandFailedException;
use App\Exceptions\AggregatedCommandThrewException;
use App\Exceptions\InvalidAggregatedCommandException;
use Throwable;

/**
 * Abstract base class for a command that aggregates a number of other commands.
 *
 * Subclasses need only populate the $commands property with the commands to run. Only commands that don't take any
 * arguments or options can (currently) be aggregated.
 */
abstract class AggregateCommand extends Command
{
    /**
     * @var array Array of commands to execute. Can be:
     * - string (just the command to execute, e.g. app:do-something)
     * - associative array with "command" and optional "arguments" elements (e.g. ["command" => "app:do-something", "arguments" -> ["--opt1", "--opt2=foo",])
     * - object with "command" and optionally "arguments" properties (e.g. new StdClass() { $command = "app:do-something"; $arguments = ["--opt1", "--opt2=foo",]; })
     */
    protected array $commands = [];

    /**
     * @var bool Set to true to abort on the first command that returns non-0.
     */
    protected bool $abortOnFail = true;

    /**
     * @return int
     * @throws \App\Exceptions\AggregatedCommandFailedException
     * @throws \App\Exceptions\AggregatedCommandThrewException
     * @throws \App\Exceptions\InvalidAggregatedCommandException
     */
    public function handle(): int
    {
        $returnCode = 0;

        foreach ($this->commands as $command) {
            if (is_string($command)) {
                $args = [];
            } else if (is_array($command)) {
                $args = $command["arguments"] ?? [];
                $command = $command["command"];
            } else if (is_object($command) && is_string($command->command ?? null)) {
                $args = $command->arguments ?? [];
                $command = $command->command;
            } else {
                throw new InvalidAggregatedCommandException("Invalid command in " . static::class, $command);
            }

            $this->line("Running ${command}");

            try {
                $returnCode = $this->call($command, $args) || $returnCode;
            } catch (Throwable $e) {
                throw new AggregatedCommandThrewException($command, $args, $e, "Command ${command} threw " . get_class($e) . ".");
            }

            if ($this->abortOnFail && 0 != $returnCode) {
                throw new AggregatedCommandFailedException($command, $args, $returnCode, "Command ${command} failed with return code ${returnCode}.");
            }
        }

        return $returnCode;
    }
}
