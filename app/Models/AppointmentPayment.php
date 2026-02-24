<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentPayment extends Model
{
    protected $fillable = [
        'appointment_id',
        'amount',
        'method',
        'notes',
        'created_by',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}