<?php

namespace App\Services\User;

use App\Models\User;
use App\Events\PasswordUpdated;
use Illuminate\Support\Facades\Hash;

class ProfilePasswordService
{
     /**
     * Update profile password.
     *
     * @param \Illuminate\Foundation\Auth\User $user
     * @param string $newPassword
     * @return void
     */
    public function update(User $user, string $newPassword): void
    {
        activity()->disableLogging();

        $user->password = Hash::make($newPassword);

        if (isset($user->password_unprotected) && $user->password_unprotected) {
            $user->password_unprotected = false;
        }

        $user->saveOrFail();

        event(new PasswordUpdated($user));
    }
}
