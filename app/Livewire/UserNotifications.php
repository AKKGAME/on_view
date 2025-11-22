<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserNotifications extends Component
{
    // အကုန်ဖျက်မည့် Function
    public function clearAll()
    {
        Auth::user()->notifications()->delete();
        
        $this->dispatch('notify', 
            type: 'success', 
            title: 'Notifications Cleared', 
            message: 'All notifications have been removed.'
        );
    }

    // တစ်ခုချင်းဖျက်မည့် Function
    public function delete($id)
    {
        $notification = Auth::user()->notifications()->find($id);
        if ($notification) {
            $notification->delete();
        }
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        if ($notification && !$notification->read_at) {
            $notification->markAsRead();
        }
    }

    public function render()
    {
        return view('livewire.user-notifications', [
            'notifications' => Auth::user()->notifications()->latest()->take(20)->get()
        ]);
    }
}