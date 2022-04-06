<?php

namespace App\Console\Commands;

use App\Rules\Password as PasswordRule;
use App\Rules\Username as UsernameRule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "make:barley-user {username} {name} {email}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create a new Barley user.";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $username = $this->argument("username");
        $name = $this->argument("name");
        $email = $this->argument("email");
        $data = compact("username", "name", "email");

        $validator = Validator::make($data, [
            "username" => ["required", new UsernameRule(), "unique:" . User::class,],
            "name" => ["required",],
            "email" => ["required", "email", "unique:" . User::class,],
        ], [
            "username.required" => "You must provide a non-empty username for the new user.",
            "username.unique" => "The username for the new user is already taken.",
            "email.required" => "You must provide a non-empty email address for the new user.",
            "email.email" => "The email address for the new user is not valid.",
            "email.unique" => "The email address for the new user is already taken.",
        ]);

        $validator->validate();
        $data["password"] = $this->ask("New user's password: ");
        $validator = Validator::make($data, [
            "password" => [new PasswordRule(),],
        ]);

        $validator->validate();

        if ($data["password"] !== $this->ask("Confirm password: ")) {
            throw new \RuntimeException("The passwords do not match.");
        }

        $data["password"] = Hash::make($data["password"]);
        $user = User::create($data);
        $this->line("Created user {$user->id}");
        return 0;
    }
}
