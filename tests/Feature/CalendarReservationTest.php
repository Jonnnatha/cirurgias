<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SurgeryRequest;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class CalendarReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_doctor_can_mark_date(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $payload = [
            'date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'duration_minutes' => 60,
            'room_number' => 1,
            'patient_name' => 'Alice',
            'procedure' => 'Appendectomy',
        ];

        $response = $this->actingAs($doctor)->post('/surgery-requests', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('surgery_requests', [
            'doctor_id' => $doctor->id,
            'status' => 'requested',
            'room_number' => 1,
        ]);
    }

    public function test_nurse_can_confirm_request(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');
        $nurse = User::factory()->create();
        $nurse->assignRole('enfermeiro');

        $request = SurgeryRequest::create([
            'doctor_id' => $doctor->id,
            'date' => now()->addDay(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'room_number' => 1,
            'duration_minutes' => 60,
            'patient_name' => 'Bob',
            'procedure' => 'Proc',
            'status' => 'requested',
            'meta' => [],
        ]);

        $response = $this->actingAs($nurse)->post("/surgery-requests/{$request->id}/approve");

        $response->assertRedirect();
        $this->assertDatabaseHas('surgery_requests', [
            'id' => $request->id,
            'status' => 'approved',
            'nurse_id' => $nurse->id,
        ]);
    }

    public function test_doctor_can_cancel_request(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $request = SurgeryRequest::create([
            'doctor_id' => $doctor->id,
            'date' => now()->addDay(),
            'start_time' => '08:00',
            'end_time' => '09:00',
            'room_number' => 1,
            'duration_minutes' => 60,
            'patient_name' => 'Carol',
            'procedure' => 'Proc',
            'status' => 'requested',
            'meta' => [],
        ]);

        $response = $this->actingAs($doctor)->delete("/surgery-requests/{$request->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('surgery_requests', [
            'id' => $request->id,
        ]);
    }

    public function test_other_doctor_blocked_after_confirmation(): void
    {
        $doctor1 = User::factory()->create();
        $doctor1->assignRole('medico');
        $doctor2 = User::factory()->create();
        $doctor2->assignRole('medico');

        SurgeryRequest::create([
            'doctor_id' => $doctor1->id,
            'date' => '2025-01-10',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'room_number' => 1,
            'duration_minutes' => 60,
            'patient_name' => 'D',
            'procedure' => 'Proc',
            'status' => 'approved',
            'meta' => [],
        ]);

        $payload = [
            'date' => '2025-01-10',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'duration_minutes' => 60,
            'room_number' => 1,
            'patient_name' => 'E',
            'procedure' => 'Proc',
        ];

        $response = $this->actingAs($doctor2)->postJson('/surgery-requests', $payload);

        $response->assertStatus(422)->assertJsonValidationErrors('start_time');
    }
}
