<?php

namespace App\Http\Controllers;

use App\Models\ExamReservation;
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
        ]);

        $reservation = ExamReservation::create([
            'doctor_id' => $request->user()->id,
            ...$data,
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

