<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class RoomDowntime extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'starts_at',
        'ends_at',
        'reason',
        'created_by',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function rescheduleRequests(): HasMany
    {
        return $this->hasMany(SurgeryRescheduleRequest::class);
    }

    public function isActive(): bool
    {
        $now = Carbon::now();
        return !$this->cancelled_at && $this->starts_at <= $now && $this->ends_at >= $now;
    }
}
