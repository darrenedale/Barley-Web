<?php

namespace App\TwoFactorAuthenticators;

use App\Contracts\SecondFactorAuthenticator as SecondFactorAuthenticatorContract;

abstract class SecondFactorAuthenticator implements SecondFactorAuthenticatorContract
{
    use SecondFactorAuthenticatorCredentials;

    /**
     * @var array|string[] The keys for the credentials to extract from the request.
     *
     * The SecondFactorAuthenticatorCredentials trait uses this to decide which data to extract from the Request it is
     * given. Redefine this in your subclass if the default is not sufficient.
     */
    protected array $secondFactorCredentials = ["token",];
}
