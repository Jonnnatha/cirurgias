<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SurgeryRequest;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_doctor_can_fetch_room_surgeries(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        SurgeryRequest::create([
            'doctor_id'        => $doctor->id,
            'date'             => '2025-01-10',
            'start_time'       => '10:00',
            'end_time'         => '11:00',
            'room_number'      => 1,
            'duration_minutes' => 60,
            'patient_name'     => 'A',
            'procedure'        => 'Proc',
            'status'           => 'approved',
        ]);
        SurgeryRequest::create([
            'doctor_id'        => $doctor->id,
            'date'             => '2025-01-10',
            'start_time'       => '12:00',
            'end_time'         => '13:00',
            'room_number'      => 2,
            'duration_minutes' => 60,
            'patient_name'     => 'B',
            'procedure'        => 'Proc',
            'status'           => 'approved',
        ]);

        $response = $this->actingAs($doctor)->getJson('/calendar?room_number=1&start_date=2025-01-01&end_date=2025-01-31');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'patient_name' => 'A',
                'room_number' => 1,
                'duration_minutes' => 60,
                'status' => 'approved',
            ]);
    }

    public function test_nurse_can_access_calendar(): void
    {
        $nurse = User::factory()->create();
        $nurse->assignRole('enfermeiro');

        $response = $this->actingAs($nurse)->get('/calendar');
        $response->assertStatus(200);
    }

    public function test_user_without_role_cannot_access_calendar(): void
    {
        $user = User::factory()->create(); // no role

        $response = $this->actingAs($user)->get('/calendar');
        $response->assertStatus(403);
    }

    public function test_room_number_nine_is_invalid(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $response = $this->actingAs($doctor)->getJson('/calendar?room_number=9&start_date=2025-01-01&end_date=2025-01-31');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['room_number']);
    }
}

