<?php

namespace Tests\Feature;

use App\Models\RoomDowntime;
use App\Models\SurgeryRequest;
use App\Models\SurgeryRescheduleRequest;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RoomDowntimeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_can_create_room_downtime_and_flag_surgeries(): void
    {
        Carbon::setTestNow('2025-01-01 08:00:00');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $surgery = SurgeryRequest::create([
            'doctor_id' => $doctor->id,
            'date' => Carbon::parse('2025-01-02'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'room_number' => 2,
            'duration_minutes' => 60,
            'patient_name' => 'Paciente Teste',
            'procedure' => 'Procedimento',
            'status' => 'approved',
            'meta' => [],
        ]);

        $payload = [
            'room_number' => 2,
            'starts_at' => '2025-01-02 07:00:00',
            'ends_at' => '2025-01-02 12:00:00',
            'reason' => 'Manutenção preventiva',
        ];

        $response = $this->actingAs($admin)->postJson('/room-downtimes', $payload);

        $response->assertCreated();

        $this->assertDatabaseHas('room_downtimes', [
            'room_number' => 2,
            'reason' => 'Manutenção preventiva',
        ]);

        $this->assertDatabaseHas('surgery_reschedule_requests', [
            'surgery_request_id' => $surgery->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $doctor->id,
            'notifiable_type' => User::class,
        ]);
    }

    public function test_alerts_endpoint_returns_pending_reschedules_for_doctor(): void
    {
        Carbon::setTestNow('2025-01-01 08:00:00');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $surgery = SurgeryRequest::create([
            'doctor_id' => $doctor->id,
            'date' => Carbon::parse('2025-01-01'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'room_number' => 3,
            'duration_minutes' => 60,
            'patient_name' => 'Paciente Impactado',
            'procedure' => 'Procedimento',
            'status' => 'approved',
            'meta' => [],
        ]);

        $downtime = RoomDowntime::create([
            'room_number' => 3,
            'starts_at' => Carbon::parse('2025-01-01 07:00:00'),
            'ends_at' => Carbon::parse('2025-01-01 18:00:00'),
            'reason' => 'Obra',
            'created_by' => $admin->id,
        ]);

        SurgeryRescheduleRequest::create([
            'surgery_request_id' => $surgery->id,
            'room_downtime_id' => $downtime->id,
            'status' => 'pending',
            'reason' => 'Sala desativada: Obra',
        ]);

        $response = $this->actingAs($doctor)->getJson('/room-downtimes/alerts');

        $response->assertOk();
        $response->assertJsonFragment([
            'patient_name' => 'Paciente Impactado',
            'room_number' => 3,
        ]);
    }
}
