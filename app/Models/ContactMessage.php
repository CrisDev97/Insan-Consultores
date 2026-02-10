<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'ip',
        'user_agent',
        'read_at',
        'contacted_at'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'contacted_at' => 'datetime',
    ];

    public function getIsReadAttribute(): bool
    {
        return !is_null($this->read_at);
    }

    public function getIsContactedAttribute(): bool
    {
        return !is_null($this->contacted_at);
    }
}
