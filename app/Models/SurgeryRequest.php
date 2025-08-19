<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurgeryRequest extends Model
{
    protected $fillable = [
        'doctor_id','nurse_id','date','start_time','end_time',
        'patient_name','procedure','status','meta'
    ];

    protected $casts = [
        'date' => 'date',
        'meta' => 'array',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function nurse()
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    public function checklistItems()
    {
        return $this->hasMany(SurgeryChecklistItem::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
