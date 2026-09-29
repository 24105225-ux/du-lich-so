<?php

namespace App\Policies;

use App\Models\ClassRegistration;
use App\Models\User;

class ClassRegistrationPolicy
{
    public function view(
        User $user,
        ClassRegistration $registration
    ): bool {
        return match ($user->role) {
            'admin' => true,

            'school' =>
                $registration
                    ->schoolClass
                    ?->school
                    ?->user_id === $user->id,

            'organizer' =>
                $registration
                    ->schedule
                    ?->program
                    ?->organizer
                    ?->user_id === $user->id,

            default => false,
        };
    }

    public function update(
        User $user,
        ClassRegistration $registration
    ): bool {
        return match ($user->role) {
            'admin' => true,

            'school' =>
                $registration
                    ->schoolClass
                    ?->school
                    ?->user_id === $user->id,

            default => false,
        };
    }
}
