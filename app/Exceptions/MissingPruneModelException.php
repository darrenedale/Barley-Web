<?php

namespace App\Exceptions;

/**
 * Exception thrown when no model has been specified in a PruneCommand subclass..
 */
class MissingPruneModelException extends PruneCommandException
{}
