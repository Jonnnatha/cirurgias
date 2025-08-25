<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'surgery_request_id',
        'path',
        'original_name',
    ];

    public function surgeryRequest()
    {
        return $this->belongsTo(SurgeryRequest::class);
    }
}
