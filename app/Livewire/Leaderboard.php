<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Leaderboard extends Component
{
    use WithPagination;

    public $filter = 'xp'; // xp, coins, rank (future expansion)

    public function render()
    {
        // XP အများဆုံး Top 20 ကို ဆွဲထုတ်မယ်
        $topUsers = User::orderBy('xp', 'desc')
            ->orderBy('coins', 'desc') // XP တူရင် Coin နဲ့ ဖြတ်မယ်
            ->paginate(20);

        // User က ကိုယ့်အဆင့်ကို သိချင်ရင် ဒီမှာရှာလို့ရတယ်
        $currentUserRank = User::where('xp', '>=', auth()->user()->xp ?? 0)->count();

        return view('livewire.leaderboard', [
            'topUsers' => $topUsers,
            'currentUserRank' => $currentUserRank,
        ]);
    }
}