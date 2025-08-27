<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SurgeryRequest;

class SurgeryRequestsSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure there is at least one doctor to own the requests
        $doctor = User::firstOrCreate(
            ['nome' => 'Dr Seed'],
            ['hierarquia' => 'medico', 'senha' => Hash::make('123')]
        );
        $doctor->assignRole('medico');

        $date = now()->addDay()->toDateString();

        $requests = [
            ['start' => '08:00', 'duration' => 60, 'room' => 1, 'patient' => 'Paciente A', 'procedure' => 'Procedimento A'],
            ['start' => '09:30', 'duration' => 45, 'room' => 2, 'patient' => 'Paciente B', 'procedure' => 'Procedimento B'],
            ['start' => '11:00', 'duration' => 30, 'room' => 3, 'patient' => 'Paciente C', 'procedure' => 'Procedimento C'],
        ];

        foreach ($requests as $r) {
            $start = $r['start'];
            $duration = $r['duration'];
            $end = date('H:i', strtotime("$date $start") + $duration * 60);

            SurgeryRequest::updateOrCreate(
                [
                    'doctor_id'   => $doctor->id,
                    'date'        => $date,
                    'start_time'  => $start,
                    'end_time'    => $end,
                ],
                [
                    'room_number'      => $r['room'],
                    'duration_minutes' => $duration,
                    'patient_name'     => $r['patient'],
                    'procedure'        => $r['procedure'],
                    'status'           => 'requested',
                    'meta'             => [],
                ]
            );
        }
    }
}
