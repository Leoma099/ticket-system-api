<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\SchoolNumber;

class TicketController extends Controller
{

    // Get all tickets
    public function index(Request $request)
    {
        $user = $request->user(); // Get authenticated user

        // Log the authenticated user and token
        Log::info('User:', [$request->user()]); // Log the authenticated user
        Log::info('Token:', [$request->bearerToken()]); // Log the token

        // Check if the user is an admin (assuming role 1 is admin)
        if ($user->role === 1)
        {
            $tickets = Ticket::all(); // Admin sees all tickets
        }
        else
        {
            // If the user is a client, return only their tickets
            $tickets = Ticket::where('account_id', $user->account->id)->get();
        }

        return response()->json($tickets);
    }

    // Create a new ticket
    public function store(Request $request)
    {
        // Ensure user is authenticated
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Ensure user has an associated account
        if (!$user->account) {
            return response()->json(['error' => 'User has no account associated.'], 400);
        }

        // Debugging Logs
        Log::info('Authenticated User:', [$user]);
        Log::info('User Account:', [$user->account]);

        $request->validate([
            'full_name' => 'required',
            'department' => 'required|integer',
            'subject' => 'required|integer',
            'priority_level' => 'required|integer',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'request_date' => 'required|date',
        ]);

        // Handle file upload
        $photoPath = $request->hasFile('photo') ? $request->file('photo')->store('photos', 'public') : null;

        // Fix the account_id assignment
        $ticket = Ticket::create([
            'account_id' => $user->account->id, // ✅ Fixed!
            'full_name' => $request->full_name,
            'department' => $request->department,
            'subject' => $request->subject,
            'priority_level' => $request->priority_level,
            'status' => 1,
            'description' => $request->description,
            'photo' => $photoPath,
            'assigned_by' => 0,
            'request_date' => $request->request_date,
            'completed_date' => $request->completed_date,
        ]);

        return response()->json($ticket, 201);
    }


    public function storeWalkIn(Request $request)
    {
        $request->validate([
            'account_id' => 'nullable',
            'full_name' => 'required',
            'department' => 'required|integer',
            'subject' => 'required|integer',
            'priority_level' => 'required|integer',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'assigned_by' => 'required|integer',
            'request_date' => 'required|date',
        ]);

        // Handle file upload
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');  // Store file
        } else {
            $photoPath = null;  // If no photo, set as null
        }

        $ticket = Ticket::create([
            'account_id' => $request->account_id,
            'full_name' => $request->full_name,
            'department' => $request->department,
            'subject' => $request->subject,
            'priority_level' => $request->priority_level,
            'status' => $request->status,
            'description' => $request->description,
            'photo' => $photoPath,
            'assigned_by' => $request->assigned_by,
            'request_date' => $request->request_date,
            'completed_date' => $request->completed_date,
        ]);

        return response()->json($ticket, 201);
    }

    // Get a single ticket
    public function show($id)
    {
        $ticket = Ticket::findOrFail($id);
        return response()->json($ticket);
    }

    // Update a ticket
    public function update(Request $request, $id)
    {
        $user = $request->user(); // Get authenticated user

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        $ticket = Ticket::findOrFail($id);
    
        // ✅ Handle file upload BEFORE updating the ticket
        $photoPath = $request->hasFile('photo') 
            ? $request->file('photo')->store('photos', 'public') 
            : $ticket->photo; // Keep existing photo if no new file is uploaded
    
        $ticket->update([
            'account_id' => $user->account->id, 
            'full_name' => $request->full_name,
            'department' => $request->department,
            'subject' => $request->subject,
            'priority_level' => $request->priority_level,
            'status' => $request->status,
            'description' => $request->description,
            'photo' => $photoPath, // ✅ Now $photoPath is correctly defined
            'assigned_by' => $request->assigned_by,
            'request_date' => $request->request_date,
            'completed_date' => $request->completed_date,
        ]);
    
        return response()->json(['message' => 'Ticket updated successfully', 'ticket' => $ticket], 200);
    }    
    

    public function storePhoto($photo)
    {
        return $photo->store('photos', 'public');
    }

    // Delete a ticket
    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();
    
        return response()->json(['message' => 'Ticket deleted successfully']);
    }

    // Method to get the number of resolved and unresolved tickets
    public function getStatusResolveAndUnresolved()
    {
        $resolvedCount = Ticket::where('status', 3)->count(); // status 3 = Resolved
        $unresolvedCount = Ticket::where('status', 4)->count(); // status 4 = Unresolved

        return response()->json([
            'resolved' => $resolvedCount,
            'unresolved' => $unresolvedCount
        ]);
    }

    public function getTicketStatus()
    {
        $pending = Ticket::where('status', 1)->count(); // status 3 = Resolved
        $inProgress = Ticket::where('status', 2)->count(); // status 4 = Unresolved
        $resolved = Ticket::where('status', 3)->count(); // status 4 = Unresolved
        $unresolved = Ticket::where('status', 4)->count(); // status 4 = Unresolved

        return response()->json([
            'pending' => $pending,
            'inProgress' => $inProgress,
            'resolved' => $resolved,
            'unresolved' => $unresolved
        ]);
    }
    
};