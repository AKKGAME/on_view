<?php

namespace App\Livewire;

use App\Models\PaymentMethod; // Import Model
use App\Models\PaymentRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Topup extends Component
{
    use WithFileUploads;

    public $payment_method; // Selected Slug (e.g. 'kpay')
    public $amount;
    public $phone_last_digits;
    public $screenshot;

    // Dynamic Display Data
    public $availableMethods = [];
    public $transfer_account_name = '';
    public $transfer_account_number = '';
    public $transfer_color = 'blue';

    public function mount()
    {
        // Active ဖြစ်သော Method များကို ဆွဲထုတ်မည်
        $this->availableMethods = PaymentMethod::where('is_active', true)->get();

        // ပထမဆုံး Method ကို Default ရွေးထားမည်
        if ($this->availableMethods->isNotEmpty()) {
            $firstMethod = $this->availableMethods->first();
            $this->setPaymentMethod($firstMethod);
        }
    }

    // Method ပြောင်းလိုက်တိုင်း အချက်အလက်တွေ လိုက်ပြောင်းမည်
    public function updatedPaymentMethod($value)
    {
        $selectedMethod = $this->availableMethods->firstWhere('slug', $value);
        if ($selectedMethod) {
            $this->setPaymentMethod($selectedMethod);
        }
    }

    public function setPaymentMethod($method)
    {
        $this->payment_method = $method->slug;
        $this->transfer_account_name = $method->account_name;
        $this->transfer_account_number = $method->account_number;
        $this->transfer_color = $method->color_class;
    }

    public function submit()
    {
        $this->validate([
            'amount' => 'required|numeric|min:500',
            'phone_last_digits' => 'required|numeric|digits_between:3,6',
            'screenshot' => 'required|image|max:2048',
            'payment_method' => 'required'
        ]);

        $path = $this->screenshot->store('payment-slips', 'public');

        PaymentRequest::create([
            'user_id' => Auth::id(),
            'payment_method' => $this->payment_method,
            'amount' => $this->amount,
            'phone_last_digits' => $this->phone_last_digits,
            'screenshot_path' => $path,
        ]);

        $this->reset(['amount', 'phone_last_digits', 'screenshot']);
        
        // Reset back to default info
        $this->mount();

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