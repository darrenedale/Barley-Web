<?php

namespace App\Console\Commands;

/**
 * Abstract base class for Barley commands.
 */
abstract class Command extends \Illuminate\Console\Command
{
    /**
     * Constant to return from handle() methods when the command was successful.
     */
    public const ExitOk = 0;
}
