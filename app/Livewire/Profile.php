<?php

namespace App\Livewire;

use App\Models\Anime;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Profile extends Component
{
    public $showEditModal = false;
    public $editName;
    public $editPhone;
    public $editPassword;
    
    // Lazy Loading အတွက် Variable
    public $readyToLoad = false;

    public function mount()
    {
        $this->editName = Auth::user()->name;
        $this->editPhone = Auth::user()->phone;
    }

    // Page ပွင့်ပြီးမှ ဒီ Function အလုပ်လုပ်မယ်
    public function loadData()
    {
        $this->readyToLoad = true;
    }

    public function updateProfile()
    {
        $user = Auth::user();

        $this->validate([
            'editName' => 'required|min:3',
            'editPhone' => 'required|numeric|unique:users,phone,' . $user->id,
            'editPassword' => 'nullable|min:6',
        ]);

        $user->name = $this->editName;
        $user->phone = $this->editPhone;

        if ($this->editPassword) {
            $user->password = Hash::make($this->editPassword);
        }

        $user->save();
        $this->showEditModal = false;
        $this->editPassword = null; 

        $this->dispatch('notify', 
            type: 'success', 
            title: 'Profile Updated', 
            message: 'Your account information has been saved.'
        );
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    public function render()
    {
        $user = Auth::user();
        $watchlist = $user->watchlist;
        $purchasedEpisodeIds = Transaction::where('user_id', $user->id)
            ->where('type', 'purchase')
            ->pluck('description')
            ->map(fn ($desc) => str_replace('ep_', '', $desc))
            ->toArray();

        $libraryAnimes = Anime::whereHas('seasons.episodes', function ($query) use ($purchasedEpisodeIds) {
            $query->whereIn('id', $purchasedEpisodeIds);
        })
        ->with(['seasons.episodes' => function ($query) use ($purchasedEpisodeIds) {
            $query->whereIn('id', $purchasedEpisodeIds);
        }])
        ->get();

        return view('livewire.profile', [
            'user' => $user,
            'libraryAnimes' => $this->readyToLoad ? $libraryAnimes : collect([]),
            'watchlist' => $this->readyToLoad ? $watchlist : collect([]),
    ]);
    }
}