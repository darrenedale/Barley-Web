<?php

namespace App\View\Components\Account;

use Illuminate\View\Component;
use function view;

class RegistrationForm extends Component
{
    public string $endpoint;
    public string $method;
    public string $registerButtonLabel;
    public string $usernamePlaceholder;
    public string $emailPlaceholder;
    public string $namePlaceholder;
    public string $passwordPlaceholder;
    public string $confirmPasswordPlaceholder;
    /** @var array<string> */
    public array $defaults;

    /**
     * Create a new component instance.
     *
     * @param string $endpoint
     * @param string $method
     * @param string $usernamePlaceholder
     * @param string $emailPlaceholder
     * @param string $namePlaceholder
     * @param string $passwordPlaceholder
     * @param string $confirmPasswordPlaceholder
     * @param string $registerButtonLabel
     * @param array<string> $defaults The default values for the form.
     */
    public function __construct(
        string $endpoint = "/register",
        string $method = "POST",
        string $usernamePlaceholder = "Username...",
        string $emailPlaceholder = "Email...",
        string $namePlaceholder = "Real name...",
        string $passwordPlaceholder = "Password...",
        string $confirmPasswordPlaceholder = "Confirm password...",
        string $registerButtonLabel = "Register",
        array $defaults = [])
    {
        if ("POST" !== strtoupper($method) && "GET" !== strtoupper($method)) {
            throw new \InvalidArgumentException("The request method for the registration form must be GET or POST.");
        }

        $this->endpoint = $endpoint;
        $this->method = $method;
        $this->usernamePlaceholder = $usernamePlaceholder;
        $this->emailPlaceholder = $emailPlaceholder;
        $this->namePlaceholder = $namePlaceholder;
        $this->passwordPlaceholder = $passwordPlaceholder;
        $this->confirmPasswordPlaceholder = $confirmPasswordPlaceholder;
        $this->registerButtonLabel = $registerButtonLabel;
        $this->defaults = $defaults;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view("components.account.registration-form");
    }
}
