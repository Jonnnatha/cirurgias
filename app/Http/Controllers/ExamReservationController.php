<?php

namespace App\Http\Controllers;

use App\Models\ExamReservation;
use App\Models\User;
use Illuminate\Http\Request;

class ExamReservationController extends Controller
{
    public function index()
    {
        return ExamReservation::all();
    }

    public function store(Request $request)
    {
        $this->authorize('create', ExamReservation::class);

        $data = $request->validate([
            'surgery_request_id' => ['nullable', 'exists:surgery_requests,id'],
            'exam_type' => ['required', 'string'],
            'date' => ['required', 'date'],
            'doctor_id' => ['nullable', 'exists:users,id'],
        ]);

        $doctorId = $request->input('doctor_id', $request->user()->id);

        if ($request->user()->hasRole('admin')) {
            $request->validate([
                'doctor_id' => ['required'],
            ]);

            abort_unless(
                User::find($doctorId)?->hasRole('medico'),
                422,
                'The selected doctor_id is invalid.'
            );
        }

        unset($data['doctor_id']);

        $reservation = ExamReservation::create([
            ...$data,
            'doctor_id' => $doctorId,
        ]);

        return response()->json($reservation, 201);
    }

    public function destroy(ExamReservation $examReservation)
    {
        $this->authorize('delete', $examReservation);

        $examReservation->delete();

        return response()->noContent();
    }
}

