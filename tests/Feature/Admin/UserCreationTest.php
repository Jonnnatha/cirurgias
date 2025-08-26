<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_can_create_nurse_and_doctor(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $nursePayload = [
            'nome' => 'Nurse',
            'hierarquia' => 'enfermeiro',
            'senha' => 'secret123',
            'senha_confirmation' => 'secret123',
        ];

        $this->actingAs($admin)->post('/admin/users', $nursePayload)->assertRedirect();
        $this->assertDatabaseHas('users', [
            'nome' => 'Nurse',
            'hierarquia' => 'enfermeiro',
        ]);
        $this->assertTrue(User::where('nome', 'Nurse')->first()->hasRole('enfermeiro'));

        $doctorPayload = [
            'nome' => 'Doctor',
            'hierarquia' => 'medico',
            'senha' => 'secret123',
            'senha_confirmation' => 'secret123',
        ];

        $this->actingAs($admin)->post('/admin/users', $doctorPayload)->assertRedirect();
        $this->assertDatabaseHas('users', [
            'nome' => 'Doctor',
            'hierarquia' => 'medico',
        ]);
        $this->assertTrue(User::where('nome', 'Doctor')->first()->hasRole('medico'));
    }

    public function test_non_admins_are_forbidden(): void
    {
        $nurse = User::factory()->create();
        $nurse->assignRole('enfermeiro');
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $this->actingAs($nurse)->get('/admin/users/create')->assertForbidden();
        $this->actingAs($doctor)->get('/admin/users/create')->assertForbidden();
    }
}
