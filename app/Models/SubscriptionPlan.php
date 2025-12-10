<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model {
    protected $fillable = ['name', 'coin_price', 'duration_days', 'description', 'is_active'];
}