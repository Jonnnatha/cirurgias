<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'surgery_request_id',
        'path',
        'uploaded_by',
    ];

    public function request()
    {
        return $this->belongsTo(SurgeryRequest::class, 'surgery_request_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
