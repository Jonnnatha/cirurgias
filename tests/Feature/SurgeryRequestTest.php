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
            'room' => 'A',
            'patient_name' => 'John Doe',
            'procedure' => 'Appendectomy',
        ];

        $response = $this->actingAs($doctor)->post('/surgery-requests', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('surgery_requests', [
            'doctor_id' => $doctor->id,
            'patient_name' => 'John Doe',
            'room' => 'A',
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
            'room' => 'A',
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
            'room' => 'A',
            'patient_name' => 'Bob',
            'procedure' => 'Proc2',
            'status' => 'requested',
            'meta' => [],
        ]);

        $version = md5_file(public_path('build/manifest.json'));
        $response = $this->actingAs($doctor)
            ->withHeader('X-Inertia', 'true')
            ->withHeader('X-Inertia-Version', $version)
            ->get('/my/surgery-requests');

        $response->assertStatus(200);
        $response->assertSee('Alice');
        $response->assertDontSee('Bob');
    }

    public function test_surgeries_in_different_rooms_can_overlap(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $date = now()->addDay()->toDateString();

        $payload1 = [
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'room' => 'A',
            'patient_name' => 'Patient1',
            'procedure' => 'Proc1',
        ];

        $payload2 = [
            'date' => $date,
            'start_time' => '10:30',
            'end_time' => '11:30',
            'room' => 'B',
            'patient_name' => 'Patient2',
            'procedure' => 'Proc2',
        ];

        $this->actingAs($doctor)->post('/surgery-requests', $payload1)->assertRedirect();
        $this->actingAs($doctor)->post('/surgery-requests', $payload2)->assertRedirect();

        $this->assertDatabaseCount('surgery_requests', 2);
    }

    public function test_surgeries_in_same_room_cannot_overlap(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $date = now()->addDay()->toDateString();

        $payload1 = [
            'date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'room' => 'A',
            'patient_name' => 'Patient1',
            'procedure' => 'Proc1',
        ];

        $payload2 = [
            'date' => $date,
            'start_time' => '10:30',
            'end_time' => '11:30',
            'room' => 'A',
            'patient_name' => 'Patient2',
            'procedure' => 'Proc2',
        ];

        $this->actingAs($doctor)->withHeader('Accept','text/html')->post('/surgery-requests', $payload1)->assertRedirect();
        $this->actingAs($doctor)->withHeader('Accept','text/html')->post('/surgery-requests', $payload2);
        $this->assertDatabaseCount('surgery_requests', 1);
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
            'room' => 'A',
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

        $this->actingAs($nurse);
        $controller = new SurgeryRequestController();
        $controller->updateChecklistItem($request, $item, new HttpRequest(['checked' => true]));

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
            'room' => 'A',
            'patient_name' => 'Patient',
            'procedure' => 'Proc',
            'status' => 'requested',
            'meta' => [],
        ]);

        $this->actingAs($nurse);
        $controller = new SurgeryRequestController();
        $controller->approve($request);

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
            'room' => 'A',
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
