<?php

namespace Tests\Feature;

use App\Models\Checklist;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ChecklistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_and_nurse_can_list_checklists(): void
    {
        $nurse = User::factory()->create();
        $nurse->assignRole('enfermeiro');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Checklist::create([
            'created_by' => $nurse->id,
            'title' => 'Pré-operatório',
            'active' => true,
        ]);

        $this->actingAs($nurse)->get('/checklists')->assertOk();
        $this->actingAs($admin)->get('/checklists')->assertOk();
    }

    public function test_non_authorized_user_cannot_list_checklists(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $this->actingAs($doctor)->get('/checklists')->assertForbidden();
    }

    public function test_admin_and_nurse_can_store_checklist(): void
    {
        $nurse = User::factory()->create();
        $nurse->assignRole('enfermeiro');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $payload = ['title' => 'Checklist A', 'active' => true];
        $this->actingAs($nurse)->post('/checklists', $payload)->assertRedirect();
        $this->assertDatabaseHas('checklists', [
            'title' => 'Checklist A',
            'created_by' => $nurse->id,
        ]);

        $payload = ['title' => 'Checklist B', 'active' => false];
        $this->actingAs($admin)->post('/checklists', $payload)->assertRedirect();
        $this->assertDatabaseHas('checklists', [
            'title' => 'Checklist B',
            'created_by' => $admin->id,
            'active' => false,
        ]);
    }

    public function test_non_authorized_user_cannot_store_checklist(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $this->actingAs($doctor)->post('/checklists', ['title' => 'X', 'active' => true])
            ->assertForbidden();
    }

    public function test_admin_and_nurse_can_update_checklist(): void
    {
        $nurse = User::factory()->create();
        $nurse->assignRole('enfermeiro');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $checklist = Checklist::create([
            'created_by' => $nurse->id,
            'title' => 'Old',
            'active' => true,
        ]);

        $this->actingAs($nurse)->put("/checklists/{$checklist->id}", [
            'title' => 'Updated by Nurse',
            'active' => false,
        ])->assertRedirect();
        $this->assertDatabaseHas('checklists', [
            'id' => $checklist->id,
            'title' => 'Updated by Nurse',
            'active' => false,
        ]);

        $this->actingAs($admin)->put("/checklists/{$checklist->id}", [
            'title' => 'Updated by Admin',
            'active' => true,
        ])->assertRedirect();
        $this->assertDatabaseHas('checklists', [
            'id' => $checklist->id,
            'title' => 'Updated by Admin',
            'active' => true,
        ]);
    }

    public function test_non_authorized_user_cannot_update_checklist(): void
    {
        $checklist = Checklist::create([
            'created_by' => User::factory()->create()->id,
            'title' => 'Original',
            'active' => true,
        ]);

        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $this->actingAs($doctor)->put("/checklists/{$checklist->id}", [
            'title' => 'New',
            'active' => false,
        ])->assertForbidden();
    }

    public function test_admin_can_destroy_checklist(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $checklist = Checklist::create([
            'created_by' => $admin->id,
            'title' => 'Delete',
            'active' => true,
        ]);

        $this->actingAs($admin)->delete("/checklists/{$checklist->id}")->assertRedirect();
        $this->assertDatabaseMissing('checklists', ['id' => $checklist->id]);
    }

    public function test_nurse_cannot_destroy_checklist(): void
    {
        $nurse = User::factory()->create();
        $nurse->assignRole('enfermeiro');
        $checklist = Checklist::create([
            'created_by' => $nurse->id,
            'title' => 'Cannot delete',
            'active' => true,
        ]);

        $this->actingAs($nurse)->delete("/checklists/{$checklist->id}")->assertForbidden();
        $this->assertDatabaseHas('checklists', ['id' => $checklist->id]);
    }
}
