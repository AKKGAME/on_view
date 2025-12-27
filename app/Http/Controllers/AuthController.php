<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
            // device_id is optional on register, but good to have if you auto-login
            'device_id' => 'nullable|string', 
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'rank' => 'Newbie',
            'coins' => 100,
            'xp' => 0,
            'device_id' => $request->device_id, // Save device ID immediately
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
    // 2. LOGIN (Single Device Logic Here)
    // ==========================================
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|numeric',
            'password' => 'required',
            'device_id' => 'required|string', // 🔥 Flutter ကနေ မဖြစ်မနေ ပို့ရမယ်
        ]);
        
        $user = User::where('phone', $request->phone)->first();

        // 1. Check User & Password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'ဖုန်းနံပါတ် သို့မဟုတ် စကားဝှက် မှားယွင်းနေပါသည်။'
            ], 401);
        }

        // 2. 🔥 Check Device ID (Single Device Enforcement)
        // အကယ်၍ DB မှာ device_id ရှိပြီး၊ ပို့လိုက်တဲ့ ID နဲ့ မတူရင် Error ပြန်မယ်
        if ($user->device_id && $user->device_id !== $request->device_id) {
            return response()->json([
                'success' => false,
                'code' => 'DEVICE_MISMATCH', // Flutter ဘက်မှာ ဒီ code ကိုစစ်ပြီး Dialog ပြမယ်
                'message' => 'ဤအကောင့်သည် အခြားဖုန်းတွင် Login ဝင်ထားပြီးဖြစ်သည်။ ကျေးဇူးပြု၍ ယခင်ဖုန်းမှ Logout လုပ်ပါ သို့မဟုတ် Admin သို့ဆက်သွယ်ပါ။'
            ], 403);
        }

        // 3. Update Device ID (If null or same device)
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
    // 3. LOGOUT
    // ==========================================
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            // 🔥 Clear Device ID so they can login on another phone later
            $user->update(['device_id' => null]);

            // Revoke current token
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}