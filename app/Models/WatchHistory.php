<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchHistory extends Model
{
    protected $fillable = ['user_id', 'episode_id'];

public function episode()
{
    return $this->belongsTo(Episode::class);
}
}
