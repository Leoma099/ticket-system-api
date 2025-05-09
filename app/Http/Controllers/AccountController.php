<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Ticket;
use App\Models\TicketNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::with('user');
    
        if ($request->has('search')) {
            $search = $request->search;
    
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%");
            });
        }
    
        $accounts = $query->get();
    
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
            'photo' => 'nullable|image',
            'date_of_birth' => 'required|date',
            'department' => 'required',
            'position' => 'required',
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

            'department' => $request->department,
            'position' => $request->position,
        ]);

        // Get Admin Roles
        $adminRoleUsers = User::where('role', 1)->get();

        foreach ($adminRoleUsers as $adminRoleUser):

            // Create Ticket Notification
            TicketNotification::create([
                'notified_to' => $adminRoleUser->account->id,
                'notified_by' => Auth::user()->account->id,
                'message' => 'you created new account',
                'data' => json_encode([
                    'module_type' => get_class($account),
                    'module_id' => $account->id,
                'is_read' => 0,
                'created_by' => Auth::id()
                ])
            ]);
        endforeach;

        return response()->json([
            'message' => 'Account and user and school number created successfully',
            'account' => $account,
        ]);
    }

    public function show($id)
    {
        $account = Account::with('user')->findOrFail($id);
    
        // Remove "public/" from the photo path if stored with "public/uploads/photos/filename.jpg"
        if ($account->photo) {
            $account->photo = str_replace('storage/uploads/photos/', 'uploads/photos/', $account->photo);
        }        
    
        return response()->json($account);
    }
    
    public function update(Request $request, $id)
    {
        $user = $request->user(); // Get authenticated user

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
            
        $account = Account::findOrFail($id);

        $account->update([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'address' => $request->address,
            'mobile_number' => $request->mobile_number,
            'date_of_birth' => $request->date_of_birth,

            'department' => $request->department,
            'position' => $request->position
        ]);
        
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

    public function getStaffWithTicketStats()
    {
        $staffAccounts = Account::with('user')
            ->whereHas('user', function ($query) {
                $query->where('role', 2); // Only staff
            })
            ->get()
            ->map(function ($account) {
                $assigned = Ticket::where('assigned_by', $account->user_id)->count();
                $resolved = Ticket::where('assigned_by', $account->user_id)
                                ->where('status', 3) // assuming 3 = resolved
                                ->count();

                return [
                    'full_name' => $account->full_name,
                    'assigned' => $assigned,
                    'resolved' => $resolved,
                ];
            });

        return response()->json($staffAccounts);
    }

    public function getStaffDataInfo()
    {
        $staff = Account::with('user') // Eager load user data
            ->whereHas('user', function ($query) {
                $query->where('role', 2); // Only fetch staff accounts
            })
            ->get();
    
        return response()->json($staff);
    }

}
