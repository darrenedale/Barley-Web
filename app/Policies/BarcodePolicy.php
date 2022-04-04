<?php

namespace App\Policies;

use App\Models\Barcode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy authorising access to Barcode models.
 *
 * Access authorisation is currently very simple but we use a policy class to make it easy to introduce more complex
 * access features in future (e.g. owners granting access to other users).
 */
class BarcodePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {}

    /**
     * Check whether a user is permitted to view a barcode.
     *
     * @param \App\Models\User $user The user requesting to view the barcode.
     * @param \App\Models\Barcode $barcode The barcode they are requesting to view.
     *
     * @return bool true if the user can view the barcode, false otherwise.
     */
    public function view(User $user, Barcode $barcode): bool
    {
        return $barcode->user_id === $user->id;
    }

    /**
     * Check whether a user is permitted to edit a barcode.
     *
     * @param \App\Models\User $user The user requesting to edit the barcode.
     * @param \App\Models\Barcode $barcode The barcode they are requesting to edit.
     *
     * @return bool true if the user can edit the barcode, false otherwise.
     */
    public function edit(User $user, Barcode $barcode): bool
    {
        return $barcode->user_id === $user->id;
    }

    /**
     * Check whether a user is permitted to delete a barcode.
     *
     * @param \App\Models\User $user The user requesting to delete the barcode.
     * @param \App\Models\Barcode $barcode The barcode they are requesting to delete.
     *
     * @return bool true if the user can delete the barcode, false otherwise.
     */
    public function delete(User $user, Barcode $barcode): bool
    {
        return $barcode->user_id === $user->id;
    }
}
