<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurgeryRescheduleRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'surgery_request_id',
        'room_downtime_id',
        'status',
        'reason',
        'notes',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function surgeryRequest(): BelongsTo
    {
        return $this->belongsTo(SurgeryRequest::class);
    }

    public function roomDowntime(): BelongsTo
    {
        return $this->belongsTo(RoomDowntime::class);
    }
}
