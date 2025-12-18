<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage; // Database အတွက်
use Illuminate\Support\Facades\Http;

class NewTopupRequest extends Notification
{
    use Queueable;

    public $paymentRequest;

    public function __construct($paymentRequest)
    {
        $this->paymentRequest = $paymentRequest;
    }

    public function via($notifiable)
    {
        return ['database']; // Database ထဲ သိမ်းမယ် (Mail သုံးချင်ရင် 'mail' ထည့်ပါ)
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'topup',
            'message' => 'New Topup: ' . $this->paymentRequest->amount . ' MMK by ' . $this->paymentRequest->user->name,
            'amount' => $this->paymentRequest->amount,
            'payment_method' => $this->paymentRequest->payment_method,
            'user_id' => $this->paymentRequest->user_id,
            'request_id' => $this->paymentRequest->id,
        ];
    }
}