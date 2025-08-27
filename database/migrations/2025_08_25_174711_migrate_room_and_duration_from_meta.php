<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('surgery_requests')->select(['id', 'meta'])->whereNotNull('meta')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $meta = json_decode($row->meta, true) ?: [];
                $room = $meta['room_number'] ?? null;
                $duration = $meta['duration_minutes'] ?? null;
                unset($meta['room_number'], $meta['duration_minutes']);

                DB::table('surgery_requests')->where('id', $row->id)->update([
                    'room_number' => $room,
                    'duration_minutes' => $duration,
                    'meta' => empty($meta) ? null : json_encode($meta),
                ]);
            }
        });
    }

    public function down(): void
    {
        // No-op: this migration is one-way.
    }
};
