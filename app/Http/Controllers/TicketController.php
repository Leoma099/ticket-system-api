<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    // Get all tickets
    public function index()
    {
        $tickets = Ticket::all();
        return response()->json($tickets);
    }    

    // Create a new ticket
    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'nullable',
            'full_name' => 'required',
            'school_number' => 'required',
            'department' => 'required|integer',
            'subject' => 'required|integer',
            'priority_level' => 'required|integer',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
            'school_number' => $request->school_number,
            'department' => $request->department,
            'subject' => $request->subject,
            'priority_level' => $request->priority_level,
            'status' => 1,
            'description' => $request->description,
            'photo' => $photoPath,
            'assigned_by' => $request->assigned_by,
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
            'school_number' => 'required',
            'department' => 'required|integer',
            'subject' => 'required|integer',
            'priority_level' => 'required|integer',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
            'school_number' => $request->school_number,
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
        $request->validate([
            'status' => 'required|integer|in:1,2,3,4',  // Status (1=Pending, 2=In-Progress, 3=Resolved, 4=Closed)
        ]);

        $ticket = Ticket::findOrFail($id);

        if (auth()->user()->role !== 1) { // Assuming 1 = Admin
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $ticket->status = $request->status;
        $ticket->save();

        return response()->json($ticket);
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
    
};