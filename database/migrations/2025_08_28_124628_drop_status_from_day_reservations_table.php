<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('day_reservations', 'status')) {
            DB::table('day_reservations')->update(['status' => 'confirmed']);

            Schema::table('day_reservations', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('day_reservations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed'])->default('pending');
        });
    }
};
