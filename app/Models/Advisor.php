<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advisor extends Model
{
    protected $fillable = ['name','email','phone','bio','is_active'];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'advisor_service')
            ->withPivot(['duration_minutes','is_active'])
            ->withTimestamps();
    }

    public function availabilities()
    {
        return $this->hasMany(AdvisorAvailability::class);
    }
}