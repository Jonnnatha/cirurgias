<?php

namespace Tests\Feature;

use App\Models\ExamReservation;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ExamReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_doctor_can_create_exam_reservation(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $payload = [
            'exam_type' => 'Raio-X',
            'date' => now()->addDay()->toDateString(),
        ];

        $this->actingAs($doctor)->postJson('/exams', $payload)
            ->assertStatus(201);

        $this->assertDatabaseHas('exam_reservations', [
            'doctor_id' => $doctor->id,
            'exam_type' => 'Raio-X',
        ]);
    }

    public function test_doctor_can_delete_own_exam_reservation(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $reservation = ExamReservation::create([
            'doctor_id' => $doctor->id,
            'exam_type' => 'Raio-X',
            'date' => now()->addDay(),
        ]);

        $this->actingAs($doctor)->deleteJson("/exams/{$reservation->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('exam_reservations', [
            'id' => $reservation->id,
        ]);
    }

    public function test_other_doctor_cannot_delete_exam_reservation(): void
    {
        $doctor1 = User::factory()->create();
        $doctor1->assignRole('medico');
        $doctor2 = User::factory()->create();
        $doctor2->assignRole('medico');

        $reservation = ExamReservation::create([
            'doctor_id' => $doctor1->id,
            'exam_type' => 'Raio-X',
            'date' => now()->addDay(),
        ]);

        $this->actingAs($doctor2)->deleteJson("/exams/{$reservation->id}")
            ->assertForbidden();
    }

    public function test_nurse_cannot_create_exam_reservation(): void
    {
        $nurse = User::factory()->create();
        $nurse->assignRole('enfermeiro');

        $payload = [
            'exam_type' => 'Raio-X',
            'date' => now()->addDay()->toDateString(),
        ];

        $this->actingAs($nurse)->postJson('/exams', $payload)
            ->assertForbidden();
    }
}

