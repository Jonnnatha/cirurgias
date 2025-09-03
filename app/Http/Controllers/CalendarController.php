<?php

namespace App\Http\Controllers;

use App\Models\DayReservation;
use App\Models\SurgeryRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->query('room_number')) {
            $data = $request->validate([
                'room_number' => ['required','integer','between:1,8'],
                'start_date'  => ['required','date'],
                'end_date'    => ['required','date','after_or_equal:start_date'],
            ]);

            $surgeries = SurgeryRequest::where('room_number', $data['room_number'])
                ->whereBetween('date', [$data['start_date'], $data['end_date']])
                ->orderBy('date')
                ->orderBy('start_time')
                ->get([
                    'id',
                    'date',
                    'start_time',
                    'end_time',
                    'patient_name',
                    'procedure',
                    'room_number',
                    'duration_minutes',
                    'status',
                ]);

            return response()->json($surgeries);
        }

        return Inertia::render('Calendar');
    }

    public function store(Request $request)
    {
        $this->authorize('create', DayReservation::class);

        $data = $request->validate([
            'date' => ['required', 'date', 'unique:day_reservations,date'],
        ]);

        $reservation = DayReservation::create([
            'doctor_id' => $request->user()->id,
            'date' => $data['date'],
        ]);

        return response()->json($reservation, 201);
    }

    public function destroy(DayReservation $dayReservation)
    {
        $this->authorize('delete', $dayReservation);

        $dayReservation->delete();

        return response()->noContent();
    }
}

