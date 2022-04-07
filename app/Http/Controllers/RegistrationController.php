<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmEmail;
use App\Models\User;
use App\Rules\Username as UsernameRule;
use App\Rules\Password as PasswordRule;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    public function register(Request $request)
    {
        $data  = $request->validate(
            [
                "name" => ["required", "string",],
                "username" => ["required", new UsernameRule(), "unique:" . User::class],
                "email" => ["required", "email", "unique:" . User::class,],
                "password" => ["required", new PasswordRule(), "confirmed",],
            ],
            [
                "name" => "You must provide a non-empty name.",
                "username.required" => "You must provide a non-empty username.",
                "username.unique" => "The username you provided is already taken.",
                "email.required" => "You must provide a valid email address.",
                "email.email" => "You must provide a valid email address.",
                "email.unique" => "The email address you provided is already in use with another account.",
                "password.required" => "You must choose a password.",
                "password.confirmed" => "Your passwords did not match.",
            ]
        );

        $data["password"] = Hash::make($data["password"]);
        $user = User::create($data);

        if (!$user) {
            // TODO failed to create user record
        }

        Event::dispatch(new Registered($user));
    }

    public function verifyEmailAddress(Request $request)
    {

    }
}
