<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSession extends Model
{
    protected $fillable = [
        'service_id',
        'session_number',
        'duration_minutes',
        'is_active',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
