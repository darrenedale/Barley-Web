<?php

namespace App\Facades;

use App\Contracts\SecondFactorAuthenticator as SecondFactorAuthenticatorContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

/**
 * @method static withUser(Authenticatable $user): static
 * @method static retrieveCredentials(Request $request): mixed
 * @method static check(): bool
 * @method static attempt(mixed $credentials): bool
 * @method static deauthenticate()
 */
class SecondFactorAuthenticator extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return SecondFactorAuthenticatorContract::class;
    }
}
