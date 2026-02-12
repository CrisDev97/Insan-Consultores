<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvisorAvailability extends Model
{
    protected $fillable = ['advisor_id','weekday','start_time','end_time','slot_minutes','is_active'];

    public function advisor()
    {
        return $this->belongsTo(Advisor::class);
    }
}