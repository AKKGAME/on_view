<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subtitle extends Model
{
    protected $fillable = ['episode_id', 'language', 'url', 'format'];

    public function episode()
    {
        return $this->belongsTo(Episode::class);
    }
}