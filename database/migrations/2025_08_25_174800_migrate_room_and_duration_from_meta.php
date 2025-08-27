<?php

use App\Models\SurgeryRequest;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SurgeryRequest::cursor()->each(function (SurgeryRequest $request) {
            $meta = $request->meta ?? [];
            $updates = [];

            if (isset($meta['room'])) {
                $updates['room_number'] = $meta['room'];
                unset($meta['room']);
            }

            if (isset($meta['duration'])) {
                $updates['duration_minutes'] = $meta['duration'];
                unset($meta['duration']);
            }

            if ($updates) {
                $updates['meta'] = $meta ?: null;
                $request->update($updates);
            }
        });
    }

    public function down(): void
    {
        SurgeryRequest::cursor()->each(function (SurgeryRequest $request) {
            $meta = $request->meta ?? [];

            if ($request->room_number !== null) {
                $meta['room'] = $request->room_number;
            }

            if ($request->duration_minutes !== null) {
                $meta['duration'] = $request->duration_minutes;
            }

            $request->update([
                'meta' => $meta,
                'room_number' => null,
                'duration_minutes' => null,
            ]);
        });
    }
};
