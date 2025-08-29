<?php

namespace App\Http\Controllers;

use App\Models\DayReservation;
use App\Models\SurgeryRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            if ($request->query('room_number')) {
                $data = $request->validate([
                    'room_number' => ['required', 'integer', 'between:1,9'],
                    'start_date'  => ['required', 'date'],
                    'end_date'    => ['required', 'date', 'after_or_equal:start_date'],
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

            $data = $request->validate([
                'start_date' => ['required', 'date'],
                'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            ]);

            $reservations = DayReservation::whereBetween('date', [$data['start_date'], $data['end_date']])
                ->orderBy('date')
                ->get(['id', 'date', 'doctor_id', 'status']);

            return response()->json($reservations);
        }

        return Inertia::render('Calendar');
    }

    public function store(Request $request)
    {
        $this->authorize('create', DayReservation::class);

        $data = $request->validate([
            'date' => ['required', 'date'],
        ]);

        if (DayReservation::whereDate('date', $data['date'])->exists()) {
            throw ValidationException::withMessages([
                'date' => __('Dia já está reservado.'),
            ]);
        }

        $reservation = DayReservation::create([
            'doctor_id' => $request->user()->id,
            'date' => $data['date'],
            'status' => 'pending',
        ]);

        return response()->json($reservation, 201);
    }

    public function confirm(DayReservation $dayReservation)
    {
        $this->authorize('confirm', $dayReservation);

        $dayReservation->update(['status' => 'confirmed']);

        return response()->json($dayReservation);
    }

    public function destroy(DayReservation $dayReservation)
    {
        $this->authorize('delete', $dayReservation);

        $dayReservation->delete();

        return response()->noContent();
    }
}

