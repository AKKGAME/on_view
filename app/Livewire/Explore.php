<?php

namespace App\Livewire;

use App\Models\Anime;
use App\Models\Genre;
use Livewire\Component;
use Livewire\WithPagination;

class Explore extends Component
{
    use WithPagination;

    public $search = '';
    public $genre = ''; // Selected Genre ID
    public $sort = 'latest'; // latest, oldest, az, za

    // Search လုပ်ရင် Page 1 ပြန်ရောက်အောင်
    public function updatedSearch() { $this->resetPage(); }
    public function updatedGenre() { $this->resetPage(); }
    public function updatedSort() { $this->resetPage(); }

    public function render()
    {
        $query = Anime::query();

        // 1. Search Logic
        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        // 2. Genre Filter Logic
        if ($this->genre) {
            $query->whereHas('genres', function ($q) {
                $q->where('genres.id', $this->genre);
            });
        }

        // 3. Sorting Logic
        switch ($this->sort) {
            case 'oldest': $query->oldest(); break;
            case 'az': $query->orderBy('title', 'asc'); break;
            case 'za': $query->orderBy('title', 'desc'); break;
            default: $query->latest(); break;
        }

        return view('livewire.explore', [
            'animes' => $query->paginate(12),
            'genres' => Genre::orderBy('name')->get(),
        ]);
    }
}