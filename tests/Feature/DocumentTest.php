<?php

namespace Tests\Feature;

use App\Models\SurgeryRequest;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function makeRequest(User $doctor): SurgeryRequest
    {
        return SurgeryRequest::create([
            'doctor_id' => $doctor->id,
            'date' => now()->addDay(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'room_number' => 1,
            'duration_minutes' => 60,
            'patient_name' => 'Patient',
            'procedure' => 'Proc',
            'status' => 'requested',
            'meta' => [],
        ]);
    }

    public function test_doctor_can_upload_valid_document(): void
    {
        Storage::fake('local');
        $doctor = User::factory()->create();
        $doctor->assignRole('admin');
        $request = $this->makeRequest($doctor);

        $file = UploadedFile::fake()->create('doc.pdf', 500, 'application/pdf');

        $response = $this->actingAs($doctor)
            ->withHeader('Accept', 'text/html')
            ->post("/surgery-requests/{$request->id}/documents", [
                'file' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('ok', 'Documento enviado!');
        $this->assertDatabaseCount('documents', 1);
        $this->assertDatabaseHas('documents', [
            'original_name' => 'doc.pdf',
        ]);
        Storage::disk('local')->assertExists('documents/'.$file->hashName());
    }

    public function test_invalid_file_type_is_rejected(): void
    {
        Storage::fake('local');
        $doctor = User::factory()->create();
        $doctor->assignRole('admin');
        $request = $this->makeRequest($doctor);

        $file = UploadedFile::fake()->create('file.txt', 100, 'text/plain');

        $response = $this->actingAs($doctor)
            ->withHeader('Accept', 'text/html')
            ->post("/surgery-requests/{$request->id}/documents", [
                'file' => $file,
            ]);
        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_invalid_file_size_is_rejected(): void
    {
        Storage::fake('local');
        $doctor = User::factory()->create();
        $doctor->assignRole('admin');
        $request = $this->makeRequest($doctor);

        $file = UploadedFile::fake()->create('big.pdf', 3000, 'application/pdf');

        $response = $this->actingAs($doctor)
            ->withHeader('Accept', 'text/html')
            ->post("/surgery-requests/{$request->id}/documents", [
                'file' => $file,
            ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('documents', 0);
    }
}
