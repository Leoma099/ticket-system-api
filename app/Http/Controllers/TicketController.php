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
            'full_name' => 'required',
            'school_number' => 'required',
            'department' => 'required',
            'subject' => 'required',
            'priority_level' => 'required|integer',
            'status' => 'required|integer',
            'description' => 'required',
            'request_date' => 'required|date',
        ]);

        $ticket = Ticket::create([
            'full_name' => $request->full_name,
            'school_number' => $request->school_number,
            'department' => $request->department,
            'subject' => $request->subject,
            'priority_level' => $request->priority_level ?? 1,
            'status' => $request->status ?? 1,
            'description' => $request->description,
            'request_date' => $request->request_date,
            'completed_date' => $request->completed_date,
        ]);

        return response()->json([
            'message' => 'Ticket created successfully',
            'ticket' => $ticket,
        ]);
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
        $ticket = Ticket::findOrFail($id);
        $ticket->update($request->all());
    
        return response()->json([
            'message' => 'Ticket updated successfully',
            'ticket' => $ticket,
        ]);
    }

    // Delete a ticket
    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();
    
        return response()->json(['message' => 'Ticket deleted successfully']);
    }
    
};