<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SurgeryRequest;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CalendarControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_calendar_returns_surgeries_for_requested_room_and_date_range(): void
    {
        $user = User::factory()->create();
        $user->assignRole('medico');

        $dateWithin = now()->addDay();
        $dateOutside = now()->addDays(3);

        SurgeryRequest::create([
            'doctor_id' => $user->id,
            'date' => $dateWithin,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'room' => 'A',
            'patient_name' => 'Inside',
            'procedure' => 'Proc1',
            'status' => 'approved',
            'meta' => [],
        ]);

        SurgeryRequest::create([
            'doctor_id' => $user->id,
            'date' => $dateOutside,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'room' => 'A',
            'patient_name' => 'Outside',
            'procedure' => 'Proc2',
            'status' => 'approved',
            'meta' => [],
        ]);

        SurgeryRequest::create([
            'doctor_id' => $user->id,
            'date' => $dateWithin,
            'start_time' => '11:00',
            'end_time' => '12:00',
            'room' => 'B',
            'patient_name' => 'OtherRoom',
            'procedure' => 'Proc3',
            'status' => 'approved',
            'meta' => [],
        ]);

        $start = $dateWithin->toDateString();
        $end = $dateWithin->toDateString();

        $response = $this->actingAs($user)->getJson("/calendar/data?room=A&start={$start}&end={$end}");

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['patient_name' => 'Inside']);
        $response->assertJsonMissing(['patient_name' => 'Outside']);
        $response->assertJsonMissing(['patient_name' => 'OtherRoom']);
    }
}
