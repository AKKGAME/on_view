<?php

namespace App\Livewire;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\WatchHistory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination; // Pagination အတွက် Trait ထည့်ထားပါတယ်

class Home extends Component
{
    use WithPagination;

    // History ဖျက်မယ့် Function
    public function removeFromHistory($animeId)
    {
        if (!Auth::check()) return;

        $user = Auth::user();

        // ၁. ဒီ Anime မှာပါတဲ့ Episode ID အကုန်လုံးကို ရှာမယ်
        $episodeIds = Episode::whereHas('season', function ($query) use ($animeId) {
            $query->where('anime_id', $animeId);
        })->pluck('id');

        // ၂. User ရဲ့ History ထဲက ဒီ Anime နဲ့ဆိုင်တာမှန်သမျှ ဖျက်မယ်
        WatchHistory::where('user_id', $user->id)
            ->whereIn('episode_id', $episodeIds)
            ->delete();

        // Custom Gaming Alert (Notification)
        $this->dispatch('notify', 
            type: 'success', 
            title: 'Successfully', 
            message: 'Removed from Continue Watching'
        );
    }

    public function render()
    {
        $continueWatching = [];

        if (Auth::check()) {
            $history = WatchHistory::where('user_id', Auth::id())
                ->with('episode.season.anime')
                ->latest('updated_at')
                ->get();

            $continueWatching = $history->unique(function ($item) {
                return $item->episode?->season?->anime_id;
            })->take(5);
        }

        // Slider အတွက် ၈ ကား သီးသန့်ယူမယ် (Random)
        $sliderAnimes = Anime::with('seasons')
                        ->where('is_completed', false) // Ongoing တွေကို ဦးစားပေးပြမယ် (Optional)
                        ->inRandomOrder()
                        ->take(8) 
                        ->get();
        
        // Grid အတွက်ကတော့ Latest အတိုင်းသွားမယ် (Pagination 12 ခုစီ)
        $latestAnimes = Anime::with('seasons')->latest()->paginate(12);

        return view('livewire.home', [
            'sliderAnimes' => $sliderAnimes, // Slider Data
            'animes' => $latestAnimes, // Grid Data
            'continueWatching' => $continueWatching,
        ]);
    }
}