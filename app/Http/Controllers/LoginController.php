<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->to("/");
    }
}
