<?php

use App\Models\RoomDowntime;
use App\Models\SurgeryRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surgery_reschedule_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SurgeryRequest::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(RoomDowntime::class)->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'accepted', 'cancelled'])->default('pending');
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surgery_reschedule_requests');
    }
};
