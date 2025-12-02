<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // POST /register
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|numeric|unique:users,phone',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'rank' => 'Newbie',
            'coins' => 100,
            'xp' => 0,
        ]);

        $token = $user->createToken('flutter-token')->plainTextToken;
        return response()->json(['token' => $token, 'user' => $user], 201);
    }

    // POST /login
    public function login(Request $request)
    {
        $request->validate(['phone' => 'required|numeric', 'password' => 'required']);
        
        $user = User::where('phone', $request->phone)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        
        $token = $user->createToken('flutter-token')->plainTextToken;
        return response()->json(['token' => $token, 'user' => $user], 200);
    }
}