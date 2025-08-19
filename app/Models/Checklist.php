<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    protected $fillable = ['created_by','title','active'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
