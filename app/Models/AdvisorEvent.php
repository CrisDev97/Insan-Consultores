<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvisorEvent extends Model
{
    protected $fillable = [
        'advisor_id','title','type','start_at','end_at','location','notes',
        'visibility','status','is_active'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function advisor()
    {
        return $this->belongsTo(Advisor::class);
    }
}