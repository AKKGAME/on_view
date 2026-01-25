<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['title', 'type', 'sort_order', 'is_active'];

    public function animes()
    {
        return $this->belongsToMany(Anime::class, 'anime_section')
                    ->withPivot('sort_order')
                    ->orderByPivot('sort_order', 'asc');
    }
}
