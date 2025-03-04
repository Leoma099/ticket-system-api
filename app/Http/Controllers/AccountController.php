<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use App\Models\SchoolNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $accounts = Account::with('user')->get();
        return response()->json($accounts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email|unique:accounts',
            'address' => 'required',
            'mobile_number' => 'required',
            'username' => 'required|unique:users',
            'password' => 'required|min:6',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'date_of_birth' => 'required|date',
        ]);

        // Handle file upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('uploads/photos', 'public');
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role, // Default is User (3)
        ]);

        $account = Account::create([
            'user_id' => $user->id,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'address' => $request->address,
            'mobile_number' => $request->mobile_number,
            'photo' => $photoPath, // Store the uploaded file path
            'date_of_birth' => $request->date_of_birth,
        ]);

        return response()->json([
            'message' => 'Account and user and school number created successfully',
            'account' => $account,
        ]);
    }

    public function show($id)
    {
        $account = Account::with('user', 'schoolNumber')->findOrFail($id);
    
        // Remove "public/" from the photo path if stored with "public/uploads/photos/filename.jpg"
        if ($account->photo) {
            $account->photo = str_replace('storage/uploads/photos/', 'uploads/photos/', $account->photo);
        }        
    
        return response()->json($account);
    }
    

    public function update(Request $request, Account $account)
    {
        $request->validate([
            'full_name' => 'sometimes|required',
            'email' => 'sometimes|required|email|unique:accounts,email,' . $account->id,
            'address' => 'sometimes|required',
            'mobile_number' => 'sometimes|required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // ✅ Delete old photo if new one is uploaded
        if ($request->hasFile('photo')) {
            if ($account->photo) {
                Storage::disk('public')->delete($account->photo);
            }
            $photoPath = $request->file('photo')->store('uploads/photos', 'public');
            $account->update(['photo' => $photoPath]);
        }

        $account->update($request->except('photo'));
        
        return response()->json(['message' => 'Account updated successfully', 'account' => $account]);
    }

    public function destroy($id)
    {
        $account = Account::find($id);
    
        if (!$account) {
            return response()->json(['error' => 'Account not found'], 404);
        }
    
        $account->delete();
    
        return response()->json(['message' => 'Account deleted successfully']);
    }
}
