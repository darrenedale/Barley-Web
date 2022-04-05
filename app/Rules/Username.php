<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Validate a value as a username.
 */
class Username implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {}

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, mixed $value): bool
    {
        return is_string($value) &&
            4 <= strlen($value) &&
            50 >= strlen($value) &&
            preg_match("/[a-zA-Z][a-zA-Z0-9_-]+/", $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return "The :attribute must be a valid username (between 4 and 50 alphanumeric characters, plus - and _, starting with a letter).";
    }
}
