<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'student_user_id',
        'advisor_id',
        'service_id',
        'session_number',
        'starts_at',
        'ends_at',
        'status',

        // pago
        'service_total',
        'paid_total',
        'payment_status',
        'balance',
        'last_payment_at',
        'payment_notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'last_payment_at' => 'datetime',
    ];

    // Opcional si tienes relaciones:
    public function service() { return $this->belongsTo(Service::class); }
    public function advisor() { return $this->belongsTo(Advisor::class); }
    public function student() { return $this->belongsTo(User::class, 'student_user_id'); }
    public function payments()
    {
        return $this->hasMany(\App\Models\AppointmentPayment::class);
    }
}