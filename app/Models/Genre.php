<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    protected $fillable = ['name', 'slug'];

public function animes()
{
    return $this->belongsToMany(Anime::class);
}
}
