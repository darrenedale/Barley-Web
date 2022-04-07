<?php

namespace App\Http\Controllers;

use App\Http\Responses\AjaxResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Controller for the login/out flow.
 */
class LoginController extends Controller
{
    /**
     * Session key for a path to redirect to on successful login.
     */
    private const LoginRedirectPathSessionKey = "login-controller.successful-login.redirect.path";

    /**
     * Session key for a named route to redirect to on successful login.
     */
    private const LoginRedirectNamedRouteSessionKey = "login-controller.successful-login.redirect.named-route";

    /**
     * Set the path to redirect to on successful login.
     *
     * The path only lasts until the next request. If the next request is a successful login, the user will be
     * redirected to the given path. If the next request is a failed login attempt, the path will be retained for one
     * more request. If the next request is any other request, the path will be forgotten.
     *
     * Setting a redirect path will remove any named route that has previously been specified as the redirect.
     *
     * @param string $path The redirect path.
     */
    public static function setSuccessfulLoginRedirectPath(string $path)
    {
        Session::flash(self::LoginRedirectPathSessionKey, $path);
        Session::forget(self::LoginRedirectNamedRouteSessionKey);
    }

    /**
     * Set the named route to redirect to on successful login.
     *
     * The named route only lasts until the next request. If the next request is a successful login, the user will be
     * redirected to the named route. If the next request is a failed login attempt, the named route will be retained
     * for one more request. If the next request is any other request, the path will be forgotten.
     *
     * Setting a redirect path will remove any named route that has previously been specified as the redirect.
     *
     * @param string $routeName The named route.
     */
    public static function setSuccessfulLoginRedirectNamedRoute(string $routeName)
    {
        Session::flash(self::LoginRedirectNamedRouteSessionKey, $routeName);
        Session::forget(self::LoginRedirectPathSessionKey);
    }

    /**
     * Attempt to log in a user.
     *
     * On success, the user will be redirected to any redirect route/path that has been set, or the home page if no
     * redirect has been set.
     *
     * @param \Illuminate\Http\Request $request The incoming request.
     *
     * @return AjaxResponse|RedirectResponse
     */
    public function login(Request $request): AjaxResponse | RedirectResponse
    {
        $credentials = $request->validate([
            "username" => ["required_without:email"],
            "password" => ["required",],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if ($request->ajax()) {
                return new AjaxResponse(AjaxResponse::StatusCodeOk, AjaxResponse::StatusOk, "Successful login.");
            }

            return
                // there is no order of precedence here - as long as no code messes with the session data there is
                // always at most one of the redirects set, so order of evaluation is not significant
                match (true) {
                    $request->session()->has(self::LoginRedirectPathSessionKey) => redirect()->to($request->session()->get(self::LoginRedirectPathSessionKey)),
                    $request->session()->has(self::LoginRedirectNamedRouteSessionKey) => redirect()->route($request->session()->get(self::LoginRedirectNamedRouteSessionKey)),
                    default => redirect()->to("/"),
                };
        }

        if ($request->ajax()) {
            return new AjaxResponse(AjaxResponse::StatusCodeError, AjaxResponse::StatusError, "Your username and/or password were not recognised.");
        }


        // make sure we don't forget any redirect that's been set
        Session::reflash();
        return redirect()
            ->back()
            ->withErrors(["username" => "Your username and/or password were not recognised.",])
            ->onlyInput("username");
    }

    /**
     * Log out any currently logged-in user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->to("/");
    }
}
