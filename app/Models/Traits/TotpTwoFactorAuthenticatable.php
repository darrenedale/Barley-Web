<?php

namespace App\Models\Traits;

use App\Exceptions\MissingTotpSeedException;
use App\TwoFactorAuthenticators\TotpAuthenticator;
use App\TwoFactorAuthenticators\TotpAuthenticatorSeedType;

/**
 * Import this trait into your TwoFactorAuthenticatable classes to implement 2FA easily.
 */
trait TotpTwoFactorAuthenticatable
{
    /**
     * Determine whether 2FA is enabled.
     *
     * The default is to check whether the "totp_seed" field for the user's record is empty. Set a 'totpSeedField'
     * property on your Authenticatable class with the name of the field to customise it, or reimplement this method.
     *
     * @return bool
     */
    public function secondFactorEnabled(): bool
    {
        return !empty($this->{TotpAuthenticator::seedField()});
    }
}
