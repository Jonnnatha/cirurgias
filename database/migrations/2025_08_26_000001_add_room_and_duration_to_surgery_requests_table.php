<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surgery_requests', function (Blueprint $table) {
            $table->unsignedSmallInteger('room_number')->after('end_time')->index();
            $table->unsignedSmallInteger('duration_minutes')->after('room_number');
        });
    }

    public function down(): void
    {
        Schema::table('surgery_requests', function (Blueprint $table) {
            $table->dropColumn(['room_number', 'duration_minutes']);
        });
    }
};
