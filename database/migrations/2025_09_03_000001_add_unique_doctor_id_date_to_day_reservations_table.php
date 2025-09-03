<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('day_reservations', function (Blueprint $table) {
            $table->dropUnique('day_reservations_date_unique');
            $table->unique(['doctor_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_reservations', function (Blueprint $table) {
            $table->dropUnique(['doctor_id', 'date']);
            $table->unique('date');
        });
    }
};
