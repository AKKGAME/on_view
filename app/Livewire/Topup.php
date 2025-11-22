<?php

namespace App\Livewire;

use App\Models\PaymentRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\PaymentMethod;

class Topup extends Component
{
    use WithFileUploads;

    public $amount;
    public $phone_last_digits;
    public $screenshot;
    public $payment_method; // Selected slug (e.g., 'kpay')
    public $availableMethods = []; // New property to store DB methods
    public $transfer_account_number;
    public $transfer_color = 'slate';

    public function mount()
    {
        // Active ဖြစ်နေသော Payment Methods များကို Database မှ ခေါ်ယူပါ
        $this->availableMethods = PaymentMethod::where('is_active', true)->get();

        // ပထမဆုံးရလာတဲ့ method ကို default အဖြစ် သတ်မှတ်ပါ။
        if ($this->availableMethods->isNotEmpty()) {
            $defaultMethod = $this->availableMethods->first();
            $this->payment_method = $defaultMethod->slug;
            $this->transfer_account_number = $defaultMethod->account_number;
            $this->transfer_color = $defaultMethod->color_class; // Initial color for info box
        }
    }

    public function updatedPaymentMethod($value)
    {
        // User က Payment Method ကို ပြောင်းလိုက်ရင် Info Box ကို update လုပ်ဖို့
        $selectedMethod = $this->availableMethods->where('slug', $value)->first();
        if ($selectedMethod) {
            $this->transfer_account_number = $selectedMethod->account_number;
            $this->transfer_color = $selectedMethod->color_class;
        }
    }

    public function submit()
    {
        $this->validate([
            'amount' => 'required|numeric|min:500',
            'phone_last_digits' => 'required|numeric|digits_between:3,6',
            'screenshot' => 'required|image|max:2048', // 2MB Max
        ]);

        // ပုံသိမ်းမယ် (storage/app/public/payment-slips ထဲရောက်မယ်)
        $path = $this->screenshot->store('payment-slips', 'public');

        PaymentRequest::create([
            'user_id' => Auth::id(),
            'payment_method' => $this->payment_method,
            'amount' => $this->amount,
            'phone_last_digits' => $this->phone_last_digits,
            'screenshot_path' => $path,
        ]);

        // Reset Form
        $this->reset(['amount', 'phone_last_digits', 'screenshot']);

        // Custom Gaming Alert ပြမယ်
        $this->dispatch('notify', 
            type: 'success', 
            title: 'Submission Successful', 
            message: 'Please wait for admin approval.'
        );
    }

    public function render()
    {
        return view('livewire.topup', [
            'history' => PaymentRequest::where('user_id', Auth::id())->latest()->get()
        ]);
    }
}