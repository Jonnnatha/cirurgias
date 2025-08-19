<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurgeryChecklistItem extends Model
{
    protected $fillable = [
        'surgery_request_id','item_text','checked','checked_at','checked_by'
    ];

    protected $casts = [
        'checked' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(SurgeryRequest::class, 'surgery_request_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
