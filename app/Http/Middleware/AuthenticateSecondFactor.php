<?php

namespace App\Http\Middleware;

use App\Contracts\TwoFactorAuthenticatable;
use App\Facades\SecondFactorAuthenticator;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class AuthenticateSecondFactor
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\AuthenticationException if two factor authentication is required and has not been passed.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($this->requiresSecondFactor($user) && !$this->checkSecondFactor($request, $user)) {
            $this->unauthenticated($request);
        }

        return $next($request);
    }

    /**
     * Check whether a user requires 2FA.
     *
     * The default returns true. You can reimplement this if some users are permitted to bypass this middleware.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user The user to check.
     *
     * @return bool
     */
    protected function requiresSecondFactor(Authenticatable $user): bool
    {
        return true;
    }

    /**
     * Check the second factor for a user.
     *
     * @param \Illuminate\Http\Request $request The incoming request.
     * @param \App\Contracts\TwoFactorAuthenticatable $user The user to check.
     *
     * @return bool
     */
    protected function checkSecondFactor(Request $request, Authenticatable $user): bool
    {
        if (!($user instanceof TwoFactorAuthenticatable) || !$user->secondFactorEnabled()) {
            return false;
        }

        $authenticator = SecondFactorAuthenticator::withUser($user);
        return $authenticator->check() || $authenticator->attempt($authenticator->retrieveCredentials($request));
    }

    /**
     * Method called when 2FA has not passed.
     *
     * @param \Illuminate\Http\Request $request The incoming request.
     *
     * @return mixed
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated(Request $request)
    {
        throw new AuthenticationException(
            'Unauthenticated.', [], $this->redirectTo($request)
        );
    }

    /**
     * Where to redirect to if 2FA has not passed.
     *
     * The default redirects to a route named "2fa.login".
     *
     * @param \Illuminate\Http\Request $request The incoming request.
     *
     * @return string|null
     */
    protected function redirectTo(Request $request): string | null
    {
        return route("2fa.login");
    }
}
