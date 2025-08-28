<?php

namespace App\Policies;

use App\Models\DayReservation;
use App\Models\User;

class DayReservationPolicy
{
    public function destroy(User $u, DayReservation $r): bool
    {
        return $u->hasAnyRole(['admin','enfermeiro']);
    }

    public function confirm(User $u, DayReservation $r): bool
    {
        return $u->hasAnyRole(['admin','enfermeiro']);
    }
}

