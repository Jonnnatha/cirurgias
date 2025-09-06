<?php

namespace App\Services;

use App\Models\SurgeryRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SurgeryScheduler
{
    public function schedule(array $data): SurgeryRequest
    {
        $date = $data['date'];
        $start = $data['start_time'];
        $end = $data['end_time'];

        return DB::transaction(function () use ($data, $date, $start, $end) {
            if (DB::connection()->getDriverName() !== 'sqlite') {
                DB::select('SELECT id FROM surgery_requests WHERE date = ? FOR UPDATE', [$date]);
            }

            $conflict = SurgeryRequest::whereDate('date', $date)
                ->whereIn('status', ['requested', 'approved'])
                ->where('start_time', '<', $end)
                ->where('end_time', '>', $start)
                ->where('room_number', $data['room_number'])
                ->first();

            if ($conflict) {
                $msg = sprintf(
                    'Conflito de horário com cirurgia na sala %s de %s às %s.',
                    $conflict->room_number,
                    substr($conflict->start_time, 0, 5),
                    substr($conflict->end_time, 0, 5)
                );
                if ($conflict->patient_name) {
                    $msg .= ' Paciente: '.$conflict->patient_name.'.';
                }
                throw ValidationException::withMessages([
                    'start_time' => $msg,
                ]);
            }

            return SurgeryRequest::create([
                'doctor_id' => $data['doctor_id'],
                'date' => $date,
                'start_time' => $start,
                'end_time' => $end,
                'room_number' => $data['room_number'],
                'duration_minutes' => $data['duration_minutes'],
                'patient_name' => $data['patient_name'],
                'procedure' => $data['procedure'],
                'status' => 'requested',
                'meta' => ['confirm_docs' => (bool) ($data['confirm_docs'] ?? false)],
            ]);
        });
    }
}
