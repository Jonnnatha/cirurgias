<?php

namespace App\Policies;

use App\Models\ExamReservation;
use App\Models\User;

class ExamReservationPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('medico');
    }

    public function delete(User $user, ExamReservation $reservation): bool
    {
        return $user->hasRole('admin') || (
            $user->hasRole('medico') && $reservation->doctor_id === $user->id
        );
    }
}

