<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\PaymentMethod;

class UtilityController extends Controller
{
    // GET /genres
    public function getGenres()
    {
        return Genre::orderBy('name')->get(); 
    }

    // GET /payment-methods
    public function getPaymentMethods()
    {
        return PaymentMethod::where('is_active', true)->get();
    }
}