<?php

namespace App\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Interface for Authenticatables that support two-factor authentication.
 */
interface TwoFactorAuthenticatable extends Authenticatable
{
    /**
     * Determine whether two-factor authentication is enabled for the authenticatable object.
     *
     * @return bool
     */
    public function secondFactorEnabled(): bool;
}
