<?php

namespace App\Livewire;

use App\Models\Anime;
use App\Models\WatchHistory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Home extends Component
{
    use WithPagination;

    // History ဖျက်မယ့် Function (ပိုမိုမြန်ဆန်အောင် ပြင်ထားသည်)
    public function removeFromHistory($animeId)
    {
        if (!Auth::check()) return;

        // anime_id ပါပြီးသားမို့ တိုက်ရိုက်ဖျက်လို့ရပါပြီ (Query အဆင့်ဆင့်ပတ်စရာမလိုတော့ပါ)
        WatchHistory::where('user_id', Auth::id())
            ->where('anime_id', $animeId)
            ->delete();

        $this->dispatch('notify', 
            type: 'success', 
            title: 'Removed', 
            message: 'Removed from Continue Watching'
        );
    }

    public function render()
    {
        $continueWatching = [];

        if (Auth::check()) {
            // ၁. Login ဝင်ထားရင် History ယူမယ်
            $continueWatching = WatchHistory::where('user_id', Auth::id())
                ->with(['anime', 'episode']) // Model မှာ anime() relation ရှိရပါမယ်
                ->latest('updated_at') // နောက်ဆုံးကြည့်တာ အပေါ်ဆုံးတင်မယ်
                ->get()
                ->unique('anime_id') // Anime တစ်ခုကို Episode တစ်ခုပဲပြမယ် (Duplicate မထပ်အောင်)
                ->take(10); // ၁၀ ကားစာပဲ ယူမယ်
        }

        // Slider အတွက် ၈ ကား (Random)
        $sliderAnimes = Anime::where('is_completed', false)
                        ->inRandomOrder()
                        ->take(8) 
                        ->get();
        
        // Grid အတွက် Latest Anime များ
        $latestAnimes = Anime::latest()->paginate(12);

        return view('livewire.home', [
            'sliderAnimes' => $sliderAnimes,
            'animes' => $latestAnimes,
            'continueWatching' => $continueWatching,
        ]);
    }
}