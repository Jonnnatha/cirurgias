<?php

namespace Tests\Feature;

use App\Models\DayReservation;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DayReservationPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_doctor_can_create_day_reservation(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $payload = ['date' => now()->addDay()->toDateString()];

        $response = $this->actingAs($doctor)->postJson('/calendar', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('day_reservations', [
            'doctor_id' => $doctor->id,
        ]);
    }

    public function test_same_doctor_cannot_reserve_same_day_twice(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $date = now()->addDay()->toDateString();

        $this->actingAs($doctor)->postJson('/calendar', ['date' => $date])
            ->assertStatus(201);

        $this->actingAs($doctor)->postJson('/calendar', ['date' => $date])
            ->assertStatus(422)
            ->assertJsonValidationErrors('date');
    }

    public function test_different_doctors_can_reserve_same_day(): void
    {
        $date = now()->addDay()->toDateString();

        $doctor1 = User::factory()->create();
        $doctor1->assignRole('medico');

        $this->actingAs($doctor1)->postJson('/calendar', ['date' => $date])
            ->assertStatus(201);

        $doctor2 = User::factory()->create();
        $doctor2->assignRole('medico');

        $this->actingAs($doctor2)->postJson('/calendar', ['date' => $date])
            ->assertStatus(201);

        $this->assertDatabaseHas('day_reservations', [
            'doctor_id' => $doctor2->id,
            'date' => $date,
        ]);
    }

    public function test_nurse_cannot_create_day_reservation(): void
    {
        $nurse = User::factory()->create();
        $nurse->assignRole('enfermeiro');

        $payload = ['date' => now()->addDay()->toDateString()];

        $this->actingAs($nurse)->postJson('/calendar', $payload)
            ->assertForbidden();
    }

    public function test_doctor_can_delete_own_day_reservation(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $reservation = DayReservation::create([
            'doctor_id' => $doctor->id,
            'date' => now()->addDay(),
        ]);

        $this->actingAs($doctor)->deleteJson("/calendar/{$reservation->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('day_reservations', [
            'id' => $reservation->id,
        ]);
    }

    public function test_nurse_cannot_delete_day_reservation(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');
        $nurse = User::factory()->create();
        $nurse->assignRole('enfermeiro');

        $reservation = DayReservation::create([
            'doctor_id' => $doctor->id,
            'date' => now()->addDay(),
        ]);

        $this->actingAs($nurse)->deleteJson("/calendar/{$reservation->id}")
            ->assertForbidden();
    }

    public function test_admin_can_delete_day_reservation(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $reservation = DayReservation::create([
            'doctor_id' => $doctor->id,
            'date' => now()->addDay(),
        ]);

        $this->actingAs($admin)->deleteJson("/calendar/{$reservation->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('day_reservations', [
            'id' => $reservation->id,
        ]);
    }
}

