<?php

namespace App\Console\Commands;

use App\Rules\Password as PasswordRule;
use App\Rules\Username as UsernameRule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

/**
 * Command to interactively create a new Barley user from the console.
 *
 * You will be prompted for the new user's password.
 */
class CreateUser extends Command
{
    /**
     * Exit code when one or more of the command-line arguments is not valid.
     */
    public const ErrInvalidArguments = 1;

    /**
     * Exit code when the provided password does not meet the password strength rules.
     */
    public const ErrInvalidPassword = 2;

    /**
     * Exit code when the provided password confirmation does not match the provided password.
     */
    public const ErrPasswordMismatch = 3;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "make:barley-user
            {--accept-any-password : Don't enforce password quality rules. Use EXTREMELY sparingly.}
            {username : The username for the new user. Must be unique.}
            {name : The real name for the new user.}
            {email : The email address for the new user. Must be unique.}";

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

        if (!$validator->passes()) {
            foreach ($validator->errors() as $parameter => $errors) {
                $this->error("The {$parameter} is not valid:");

                foreach ($errors as $error) {
                    $this->error("   {$error}");
                }
            }

            return self::ErrInvalidArguments;
        }

        $data["password"] = $this->secret("New user's password");

        if (!$this->option("accept-any-password")) {
            $validator = Validator::make($data, ["password" => [new PasswordRule(),],]);

            if (!$validator->passes()) {
                $this->error($validator->errors()->first("password"));
                return self::ErrInvalidPassword;
            }
        }

        if ($data["password"] !== $this->secret("Confirm password")) {
            $this->error("The passwords do not match.");
            return self::ErrPasswordMismatch;
        }

        $data["password"] = Hash::make($data["password"]);
        $user = User::create($data);
        $this->line("Created user {$user->id}");
        return self::ExitOk;
    }
}
