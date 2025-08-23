<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SurgeryRequest;
use App\Models\SurgeryChecklistItem;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use App\Http\Controllers\SurgeryRequestController;
use Illuminate\Http\Request as HttpRequest;
use Tests\TestCase;

class SurgeryRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_doctor_can_create_surgery_request(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $payload = [
            'date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'patient_name' => 'John Doe',
            'procedure' => 'Appendectomy',
        ];

        $response = $this->actingAs($doctor)->post('/surgery-requests', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('surgery_requests', [
            'doctor_id' => $doctor->id,
            'patient_name' => 'John Doe',
            'status' => 'requested',
        ]);
    }

    public function test_doctor_lists_only_their_requests(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');
        $other = User::factory()->create();
        $other->assignRole('medico');

        SurgeryRequest::create([
            'doctor_id' => $doctor->id,
            'date' => now()->addDay(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'patient_name' => 'Alice',
            'procedure' => 'Proc1',
            'status' => 'requested',
            'meta' => [],
        ]);
        SurgeryRequest::create([
            'doctor_id' => $other->id,
            'date' => now()->addDays(2),
            'start_time' => '12:00',
            'end_time' => '13:00',
            'patient_name' => 'Bob',
            'procedure' => 'Proc2',
            'status' => 'requested',
            'meta' => [],
        ]);

        $response = $this->actingAs($doctor)->get('/my/surgery-requests');

        $response->assertStatus(200);
        $response->assertSee('Alice');
        $response->assertDontSee('Bob');
    }

    public function test_nurse_can_update_checklist_item(): void
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
            'patient_name' => 'Patient',
            'procedure' => 'Proc',
            'status' => 'requested',
            'meta' => [],
        ]);

        $item = SurgeryChecklistItem::create([
            'surgery_request_id' => $request->id,
            'item_text' => 'Item 1',
            'checked' => false,
        ]);

        $response = $this->actingAs($nurse)->put(
            "/surgery-requests/{$request->id}/checklist-items/{$item->id}",
            ['checked' => true]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('surgery_checklist_items', [
            'id' => $item->id,
            'checked' => true,
            'checked_by' => $nurse->id,
        ]);
    }

    public function test_nurse_can_approve_request(): void
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
            'patient_name' => 'Patient',
            'procedure' => 'Proc',
            'status' => 'requested',
            'meta' => [],
        ]);

        $response = $this->actingAs($nurse)->post(
            "/surgery-requests/{$request->id}/approve"
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('surgery_requests', [
            'id' => $request->id,
            'status' => 'approved',
            'nurse_id' => $nurse->id,
        ]);
    }

    public function test_nurse_can_reject_request(): void
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
            'patient_name' => 'Patient',
            'procedure' => 'Proc',
            'status' => 'requested',
            'meta' => [],
        ]);

        $this->actingAs($nurse);
        $controller = new SurgeryRequestController();
        $controller->reject($request, new HttpRequest(['reason' => 'No beds']));

        $this->assertEquals('rejected', $request->fresh()->status);
        $this->assertEquals($nurse->id, $request->fresh()->nurse_id);
        $this->assertEquals('No beds', $request->fresh()->meta['reject_reason']);
    }
}
