<?php

namespace App\TwoFactorAuthenticators;

use App\Contracts\TwoFactorAuthenticatable;
use App\Util\IntegerTotp;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TotpAuthenticator extends SecondFactorAuthenticator
{
    public const ConfigKey = "2fa.authenticators.totp";
    public const CredentialsKey = "totp_password";

    protected const IndicatorSessionKey = "2fa.totpauthenticator.authenticated";

    /**
     * @var array|string[] Property to tell base class how to extract credentials from a Request.
     */
    protected array $secondFactorCredentials = [self::CredentialsKey,];

    protected int $m_passwordDigits;
    protected TotpAuthenticatorSeedType $m_seedType;
    protected ?Authenticatable $m_user;
    protected ?string $m_seed;

    public function __construct(?TwoFactorAuthenticatable $user = null)
    {
        $this->m_user = $user ?? Auth::user();
        $this->m_seed = null;
        $config = $this->config();
        $this->m_seedType = $config["seed-type"];
        $this->m_passwordDigits = $config["digits"];
    }

    /**
     * @param string|null $key
     * @param $default
     *
     * @return mixed
     */
    protected static function config(string $key = null, $default = null): mixed
    {
        $config = config(self::ConfigKey);

        if (isset($key)) {
            return $config[$key] ?? $default;
        }

        return $config;
    }

    public static function seedField(): string
    {
        return self::config("seed-field");
    }

    public function user(): Authenticatable
    {
        return $this->m_user;
    }

    public function digits(): int
    {
        return $this->m_passwordDigits;
    }

    public function setDigits(int $digits)
    {
        $this->m_passwordDigits = $digits;
    }

    public function seed(): ?string
    {
        return $this->m_seed;
    }

    public function setSeed(?string $seed, TotpAuthenticatorSeedType $type = TotpAuthenticatorSeedType::Base32)
    {
        $this->m_seed = $seed;
        $this->m_seedType = $type;
    }

    public function seedType(): TotpAuthenticatorSeedType
    {
        return $this->m_seedType;
    }

    /**
     * Tell the authenticator how to decode the seed.
     *
     * Set to null to revert to the type specified in the config.
     *
     * @param TotpAuthenticatorSeedType|null $type
     */
    public function setSeedType(TotpAuthenticatorSeedType $type = null)
    {
        $this->m_seedType = $type ?? self::config("seed-type");
    }

    public function check(): bool
    {
        if (!isset($this->m_user)) {
            return false;
        }

        $key = self::IndicatorSessionKey . ".{$this->m_user->getAuthIdentifier()}";
        return Session::has($key) && true === Session::get($key, false);
    }

    /**
     * @throws \App\Exceptions\InvalidTotpIntervalException
     * @throws \App\Exceptions\InvalidTotpDigitsException
     */
    public function attempt(mixed $credentials): bool
	{
        if (!isset($credentials[self::CredentialsKey])) {
            return false;
        }

        $seed = $this->m_seed ?? $this->m_user->{$this->config("seed-field")};

        if (!isset($seed)) {
            return false;
        }

        $totp = new IntegerTotp($this->m_passwordDigits, "");
        $totp->setInterval($this->config("interval"));
        $totp->setBaseline($this->config("base-time"));

        $mutator = match ($this->m_seedType) {
            TotpAuthenticatorSeedType::Raw => "setSeed",
            TotpAuthenticatorSeedType::Base32 => "setBase32Seed",
            TotpAuthenticatorSeedType::Base64 => "setBase64Seed",
        };

        $totp->$mutator($seed);
        $authenticated = $credentials[self::CredentialsKey] === $totp->currentCode();
        Session::put(self::IndicatorSessionKey . "{$this->m_user->getAuthIdentifier()}", $authenticated);
        return $authenticated;
	}

    public function deauthenticate()
    {
        Session::remove(self::IndicatorSessionKey . "{$this->m_user->getAuthIdentifier()}");
    }

    public function withUser(Authenticatable $user): static
    {
        $this->m_user = $user;
        return $this;
    }
}
