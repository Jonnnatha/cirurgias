<?php

namespace App\Http\Controllers;

use App\Models\DayReservation;

class DayReservationController extends Controller
{
    public function confirm(DayReservation $reservation)
    {
        $this->authorize('confirm', $reservation);

        $reservation->update(['confirmed' => true]);

        return back()->with('ok', 'Reserva confirmada.');
    }

    public function destroy(DayReservation $reservation)
    {
        $this->authorize('destroy', $reservation);

        $reservation->delete();

        return back()->with('ok', 'Reserva desmarcada.');
    }
}

