<?php

namespace App\Http\Auth;

use Illuminate\Auth\EloquentUserProvider;

/**
 * Custom user provider for authentication with the Barley app.
 */
class UserProvider extends EloquentUserProvider
{
    /**
     * Identify the user based on provided credentials.
     *
     * The user can login by providing either their username or their email address. In both cases, the value is
     * expected to be in the "username" element. If attempting to identify the user by the username field does not work,
     * and if the username element is a valid email address, the username is used searched in the email field instead.
     *
     * @param array $credentials
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $user = parent::retrieveByCredentials($credentials);

        if (!$user && isset($credentials["username"]) && filter_var($credentials["username"], FILTER_VALIDATE_EMAIL)) {
            $credentials["email"] = $credentials["username"];
            unset($credentials["username"]);
            $user = parent::retrieveByCredentials($credentials);
        }

        return $user;
    }
}
