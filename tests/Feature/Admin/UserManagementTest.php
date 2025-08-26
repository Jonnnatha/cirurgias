<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_can_list_users(): void
    {
        $this->markTestSkipped('Inertia assets not available in test environment');
    }

    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create(['nome' => 'Old', 'hierarquia' => 'enfermeiro']);
        $user->assignRole('enfermeiro');

        $payload = [
            'nome' => 'New Name',
            'hierarquia' => 'medico',
        ];

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}", $payload)
            ->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nome' => 'New Name',
            'hierarquia' => 'medico',
        ]);

        $this->assertTrue(User::find($user->id)->hasRole('medico'));
    }

    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create(['hierarquia' => 'enfermeiro']);
        $user->assignRole('enfermeiro');

        $this->actingAs($admin)
            ->delete("/admin/users/{$user->id}")
            ->assertRedirect('/admin/users');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
