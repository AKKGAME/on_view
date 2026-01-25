<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    // ==========================================
    // 1. REGISTER
    // ==========================================
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|numeric|unique:users,phone',
            'password' => 'required|string|min:6',
            'device_id' => 'nullable|string', 
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'rank' => 'Newbie',
            'coins' => 0,
            'xp' => 0,
            'device_id' => $request->device_id,
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    // ==========================================
    // 2. LOGIN (Single Device Logic)
    // ==========================================
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric',
            'password' => 'required',
            'device_id' => 'required|string', 
        ]);
        
        $user = User::where('phone', $request->phone)->first();

        // 1. Check User & Password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'ဖုန်းနံပါတ် သို့မဟုတ် စကားဝှက် မှားယွင်းနေပါသည်။'
            ], 401);
        }

        // 2. Check Device ID (Single Device Enforcement)
        if ($user->device_id && $user->device_id !== $request->device_id) {
            return response()->json([
                'success' => false,
                'code' => 'DEVICE_MISMATCH',
                'message' => 'ဤအကောင့်သည် အခြားဖုန်းတွင် Login ဝင်ထားပြီးဖြစ်သည်။'
            ], 403);
        }

        // 3. Update Device ID
        $user->update(['device_id' => $request->device_id]);
        
        // 4. Create Token
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ], 200);
    }

    // ==========================================
    // 3. UPDATE PROFILE (NEW 🔥)
    // ==========================================
    public function updateProfile(Request $request)
    {
        $user = $request->user(); // Get Authenticated User

        $request->validate([
            'name' => 'required|string|max:255',
            // ဖုန်းနံပါတ်က Unique ဖြစ်ရမယ်၊ ဒါပေမယ့် ကိုယ့် ID ဆိုရင် ခွင့်ပြုမယ်
            'phone' => 'required|numeric|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:6', // Optional
        ]);

        // Basic Info Update
        $user->name = $request->name;
        $user->phone = $request->phone;

        // Password ပါလာမှသာ ပြောင်းမယ်
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
    }

    // ==========================================
    // 4. LOGOUT
    // ==========================================
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            // Clear Device ID
            $user->update(['device_id' => null]);
            // Revoke Token
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}