<?php

namespace App\Http\Controllers;

use App\Models\SurgeryRequest;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'room'  => ['required','string'],
            'start' => ['required','date'],
            'end'   => ['required','date','after_or_equal:start'],
        ]);

        $surgeries = SurgeryRequest::where('room', $data['room'])
            ->whereDate('date', '>=', $data['start'])
            ->whereDate('date', '<=', $data['end'])
            ->get();

        return response()->json($surgeries);
    }
}
