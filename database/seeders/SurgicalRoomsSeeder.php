<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SurgicalRoomsSeeder extends Seeder
{
    public function run(): void
    {
        $table = null;
        if (Schema::hasTable('surgical_rooms')) {
            $table = 'surgical_rooms';
        } elseif (Schema::hasTable('rooms')) {
            $table = 'rooms';
        }

        if (!$table) {
            return;
        }

        for ($i = 1; $i <= 9; $i++) {
            DB::table($table)->updateOrInsert(
                ['number' => $i],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
