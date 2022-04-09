<?php

namespace App\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

/**
 * Contract for two-factor authenticators.
 *
 * A TwoFactorAuthenticatable must provide an instance of this interface that can check the credentials for the
 * Authenticatable against some externally-provided credentials (e.g. from a HTTP Request).
 */
interface SecondFactorAuthenticator
{
    /**
     * Extract credentials from a request.
     *
     * @param \Illuminate\Http\Request $request The request that contains the credentials.
     *
     * @return mixed The credentials.
     */
    public function retrieveCredentials(Request $request): mixed;

    /**
     * Manually specify the TwoFactorAuthenticatable instance to work with.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     *
     * @return $this
     */
    public function withUser(Authenticatable $user): static;

    /**
     * Check whether the user has successfully authenticated.
     *
     * @return bool
     */
    public function check(): bool;

    /**
     * Attempt second factor authentication with specified credentials.
     *
     * @param mixed $credentials The credentials to use to attempt authentication.
     *
     * @return bool
     */
    public function attempt(mixed $credentials): bool;

    /**
     * De-authenticate the second factor.
     *
     * Implementations should use this to clear any cache of the Authenticatable's authenticated status.
     */
    public function deauthenticate();
}
