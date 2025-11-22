<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnimeRequest extends Model
{
    protected $fillable = ['user_id', 'title', 'note', 'status'];

public function user()
{
    return $this->belongsTo(User::class);
}
}
