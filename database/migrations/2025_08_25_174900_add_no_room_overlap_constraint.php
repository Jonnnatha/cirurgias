<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');
            DB::statement(<<<SQL
                ALTER TABLE surgery_requests
                ADD CONSTRAINT no_room_time_overlap
                EXCLUDE USING GIST (
                    room_number WITH =,
                    tstzrange(
                        (date + start_time),
                        (date + end_time)
                    ) WITH &&
                )
                WHERE (status IN (\'requested\', \'approved\'));
            SQL);
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE surgery_requests DROP CONSTRAINT IF EXISTS no_room_time_overlap');
        }
    }
};
