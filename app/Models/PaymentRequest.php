<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{
    protected $fillable = [
        'user_id', 'payment_method', 'amount', 'phone_last_digits', 'screenshot_path', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}