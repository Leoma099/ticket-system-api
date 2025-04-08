<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'id' => $user->id,

            'full_name' => optional($user->account)->full_name,
            'email' => optional($user->account)->email,
            'department' => optional($user->account)->department,
            'position' => optional($user->account)->position,
            'mobile_number' => optional($user->account)->mobile_number,
            'address' => optional($user->account)->address,
            'date_of_birth' => optional($user->account)->date_of_birth,

            'photo' => optional($user->account)->photo,
            'role' => $user->role,
            'username' => $user->username,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
};

