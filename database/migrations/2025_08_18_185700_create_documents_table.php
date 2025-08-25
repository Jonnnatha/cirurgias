<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('surgery_request_id')
                ->nullable()
                ->constrained('surgery_requests')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $t->string('path');
            $t->string('original_name');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
