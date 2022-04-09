<?php

namespace App\Exceptions;

use App\Contracts\TwoFactorAuthenticatable;

/**
 * Exception thrown when a TwoFactorAuthenticatable indicates it has 2FA enabled but provides no seed.
 */
class MissingTotpSeedException extends \Exception
{
    private TwoFactorAuthenticatable $m_authenticatable;

    public function __construct(TwoFactorAuthenticatable $authenticatable, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->m_authenticatable = $authenticatable;
    }

    public function getAuthenticatable(): TwoFactorAuthenticatable
    {
        return $this->m_authenticatable;
    }
}
