<?php

namespace App\Http\Controllers;

use App\Contracts\TwoFactorAuthenticatable;
use App\Facades\SecondFactorAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class LoginController extends Controller
{
    private const LoginRedirectPathSessionKey = "login-controller.successful-login.redirect.path";
    private const LoginRedirectNamedRouteSessionKey = "login-controller.successful-login.redirect.named-route";

    public static function setSuccessfulLoginRedirectPath(string $path)
    {
        Session::flash(self::LoginRedirectPathSessionKey, $path);
        Session::forget(self::LoginRedirectNamedRouteSessionKey);
    }

    public static function setSuccessfulLoginRedirectNamedRoute(string $routeName)
    {
        Session::flash(self::LoginRedirectNamedRouteSessionKey, $routeName);
        Session::forget(self::LoginRedirectPathSessionKey);
    }

    public function login(Request $request): Response | RedirectResponse
    {
        $credentials = $request->validate([
            "username" => ["required_without:email"],
            "password" => ["required",],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if ($request->ajax()) {
                // TODO return AJAX response
            }

            return
                match (true) {
                    $request->session()->has(self::LoginRedirectPathSessionKey) => redirect()->to($request->session()->get(self::LoginRedirectPathSessionKey)),
                    $request->session()->has(self::LoginRedirectNamedRouteSessionKey) => redirect()->route($request->session()->get(self::LoginRedirectNamedRouteSessionKey)),
                    default => redirect()->to("/"),
                };
        }

        $user = Auth::user();

        if (($user instanceof TwoFactorAuthenticatable) && $user->secondFactorEnabled()) {
            return $this->secondFactorLogin($request);
        }

        if ($request->ajax()) {
            // TODO return AJAX response
        }

        // make sure we don't forget any redirect that's been set
        Session::reflash();
        return redirect()
            ->back()
            ->withErrors(["username" => "Your username and/or password were not recognised.",])
            ->onlyInput("username");
    }

    public function secondFactorLogin(Request $request, TwoFactorAuthenticatable $user = null): Response | RedirectResponse
    {
        if (!$user) {
            $user = Auth::user();

            if (!($user instanceof TwoFactorAuthenticatable)) {
                // make sure we don't forget any redirect that's been set
                Session::reflash();
                return back()->withErrors("Two factor authentication not supported.");
            }
        }

        if (!SecondFactorAuthenticator::withUser($user)->attempt(SecondFactorAuthenticator::retrieveCredentials($request))) {
            return redirect()->to("2fa.login");
        }

        return back()->with("message", "Passed 2FA.");
    }

    public function showTwoFactorLoginForm(Request $request): View
    {
        return view("2fa-form");
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        SecondFactorAuthenticator::deauthenticate();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->to("/");
    }
}
