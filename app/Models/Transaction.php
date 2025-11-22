<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    // ဒီနေရာမှာ Data ဖြည့်ခွင့်ပြုမယ့် Field တွေ ကြေညာပေးရမယ်
    protected $fillable = [
        'user_id',
        'type',        // purchase (or) topup
        'amount',      // coin ပမာဏ
        'description', // ဘာဝယ်တာလဲ မှတ်တမ်း
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}