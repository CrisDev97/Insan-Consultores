<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'title',
        'description',
        'includes',
        'objectives',
        'sessions_count',
        'price',
        'image_1_path',
        'image_2_path',
        'position',
        'is_active',
    ];

    protected $casts = [
        'includes' => 'array',
        'objectives' => 'array',
        'is_active' => 'boolean',
        'sessions_count' => 'integer',
        'price' => 'decimal:2',
    ];

    public function sessions()
    {
        return $this->hasMany(\App\Models\ServiceSession::class)->orderBy('session_number');
    }

}
