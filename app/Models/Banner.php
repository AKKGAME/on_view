<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_url',
        'link_url',
        'order',
        'is_active',
        'start_date',
        'end_date',
    ];

    // ✅ Date fields တွေကို Carbon Object အဖြစ် အလိုအလျောက် ပြောင်းလဲရန်
    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];
}