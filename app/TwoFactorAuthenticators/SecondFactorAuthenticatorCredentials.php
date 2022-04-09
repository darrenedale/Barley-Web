<?php

namespace App\TwoFactorAuthenticators;

use Illuminate\Http\Request;

trait SecondFactorAuthenticatorCredentials
{
    /**
     * Default implementation for retrieveCredentials().
     *
     * Expects the class utilising the trait to provide a $secondFactorCredentials property, an array of keys to
     * retrieve from the request that will contain the necessary credentials.
     *
     * @param \Illuminate\Http\Request $request The request to extract from.
     *
     * @return array The credentials.
     */
    public function retrieveCredentials(Request $request): array
    {
        return $request->only($this->secondFactorCredentials);
    }
}
