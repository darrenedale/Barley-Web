<?php

namespace App\View\Components\Account;

use Illuminate\View\Component;

class LoginForm extends Component
{
    public string $endpoint;
    public string $method;
    public string $loginButtonLabel;
    public string $usernamePlaceholder;
    public string $passwordPlaceholder;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        string $endpoint = "/login",
        string $method = "POST",
        string $usernamePlaceholder = "Username or email...",
        string $passwordPlaceholder = "Password...",
        string $loginButtonLabel = "Log In")
    {
        if ("POST" !== strtoupper($method) && "GET" !== strtoupper($method)) {
            throw new \InvalidArgumentException("The request method for the login form must be GET or POST.");
        }

        $this->endpoint = $endpoint;
        $this->method = $method;
        $this->loginButtonLabel = $loginButtonLabel;
        $this->usernamePlaceholder = $usernamePlaceholder;
        $this->passwordPlaceholder = $passwordPlaceholder;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view("components.account.login-form");
    }
}
