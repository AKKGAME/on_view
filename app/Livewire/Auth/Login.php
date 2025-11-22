<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public $phone;
    public $password;

    public function login()
    {
        $this->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt(['phone' => $this->phone, 'password' => $this->password])) {
            session()->regenerate();
            return redirect()->route('home');
        }

        $this->addError('phone', 'Invalid credentials or account not found.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}