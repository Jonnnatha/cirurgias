<?php

namespace App\Policies;

use App\Models\DayReservation;
use App\Models\User;

class DayReservationPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('medico');
    }

    public function confirm(User $user, DayReservation $reservation): bool
    {
        return $user->hasAnyRole(['enfermeiro', 'admin']);
    }

    public function delete(User $user, DayReservation $reservation): bool
    {
        return $user->hasRole('medico') && $reservation->doctor_id === $user->id;
    }
}

