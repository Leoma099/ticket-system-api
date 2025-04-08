<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MyAccountController extends Controller
{
    public function index()
    {
        return response()->json(User::with('account')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        return response()->json($user);
    }

    public function show(User $user)
    {
        return response()->json($user->load('account'));
    }

    public function update(Request $request, User $user)
    {
        $user->update($request->only(['username', 'password', 'locked']));
        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function me()
    {
        $user = Auth::user(); // Get the authenticated user

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'id' => $user->id,
            'account_id' => $user->account ? $user->account->id : null,
            'username' => $user->username,
            'full_name' => $user->account ? $user->account->full_name : null,
        ]);
    }
}
