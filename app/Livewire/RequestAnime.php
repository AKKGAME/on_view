<?php

namespace App\Livewire;

use App\Models\AnimeRequest;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Filament\Notifications\Notification;

class RequestAnime extends Component
{
    public $title;
    public $note;
    public $cost = 50; // Request တစ်စောင် 50 coins

    public function submit()
    {
        $this->validate([
            'title' => 'required|min:3|max:255',
            'note' => 'nullable|max:500',
        ]);

        $user = Auth::user();

        // Coin လောက်လား စစ်မယ်
        if ($user->coins < $this->cost) {
            Notification::make()->title('Insufficient Coins')->body("You need {$this->cost} coins to make a request.")->danger()->send();
            return;
        }

        // Coin ဖြတ်မယ်
        $user->decrement('coins', $this->cost);

        // Database ထဲထည့်မယ်
        AnimeRequest::create([
            'user_id' => $user->id,
            'title' => $this->title,
            'note' => $this->note,
        ]);

        // Transaction မှတ်မယ်
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'purchase', // or 'service'
            'amount' => $this->cost,
            'description' => 'Requested Anime: ' . $this->title,
        ]);

        $this->reset(['title', 'note']);

        $this->dispatch('notify', 
            type: 'success', 
            title: 'Request Submitted', 
            message: 'Your anime request has been sent to admins.'
        );
    }

    public function render()
    {
        // User ရဲ့ အရင် Request တွေကိုပါ ပြန်ပြမယ်
        return view('livewire.request-anime', [
            'myRequests' => AnimeRequest::where('user_id', Auth::id())->latest()->get()
        ]);
    }
}