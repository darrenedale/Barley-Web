<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Ensure a password satisfies all of these rules:
 * - has 8+ characters
 * - has at least one alpha character
 * - has at least one numeric character
 * - has at least one symbol character
 */
class Password implements Rule
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
    public function passes($attribute, mixed $value)
    {
        return is_string($value) &&
            8 <= strlen($value) &&
            preg_match("/[a-zA-Z]/", $value) &&
            preg_match("/[0-9]/", $value) &&
            preg_match("/[*&^%\$£=+_(),.<>\/?@;:\[\]\\\|{}-] /", $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return "The :attribute must be at least 8 characters in length and must contain at least one letter, one number and one symbol.";
    }
}
