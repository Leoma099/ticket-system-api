<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketNotification;
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

        if (in_array($user->role, [1])) {
            // Admin and Staff see all tickets
            $query = Ticket::query();
        } else {
            // Clients see only their own tickets
            $query = Ticket::where('account_id', $user->account->id)
                           ->whereIn('approval_status', [2, 3]);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $columns = ['ticket_order', 'request_date', 'completed_date'];
        
            $query->where(function ($q) use ($columns, $search) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            });
        }
        
        $tickets = $query->get();
        
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
            'ticket_order' => $request->ticket_order,
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

        // Get Admin Roles
        $adminRoleUsers = User::where('role', 1)->get();

        foreach ($adminRoleUsers as $adminRoleUser):

            // Create Ticket Notification
            TicketNotification::create([
                'notified_to' => $adminRoleUser->account->id,
                'notified_by' => Auth::user()->account->id,
                'message' => $ticket->full_name . 'created a new ticket ' . $ticket->ticket_order,
                'data' => json_encode([
                    'module_type' => get_class($ticket),
                    'module_id' => $ticket->id,
                'is_read' => 0,
                'created_by' => Auth::id()
                ])
            ]);
        endforeach;

        // Get Client Roles
        $clientRoleUsers = User::where('role', 3)->get();

        foreach ($clientRoleUsers as $clientRoleUser):

            // Create Ticket Notification
            TicketNotification::create([
                'notified_to' => $clientRoleUser->account->id,
                'notified_by' => Auth::user()->account->id,
                'message' => ' you created a new ticket ' . $ticket->ticket_order,
                'data' => json_encode([
                    'module_type' => get_class($ticket),
                    'module_id' => $ticket->id,
                'is_read' => 0,
                'created_by' => Auth::id()
                ])
            ]);
        endforeach;
        
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
            'ticket_order' => $request->ticket_order,
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

        // Get Admin Roles
        $adminRoleUsers = User::where('role', 1)->get();

        foreach ($adminRoleUsers as $adminRoleUser):

            // Create Ticket Notification
            TicketNotification::create([
                'notified_to' => $adminRoleUser->account->id,
                'notified_by' => null,
                'message' => 'you created a new ticket ' . $ticket->ticket_order,
                'data' => json_encode([
                    'module_type' => get_class($ticket),
                    'module_id' => $ticket->id,
                'is_read' => 0,
                'created_by' => Auth::id()
                ])
            ]);
        endforeach;

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
        $user = Auth::user();

        if ($user->role == 1) return $this->adminUpdate($request, $id);
        else if ($user->role == 2) return $this->staffUpdate($request, $id);
        else return abort(403, 'You cannot update this resource data.');
    }  
    
    // Update a ticket
    public function adminUpdate($request, $id)
    {
        $user = $request->user(); // Get authenticated user

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        $ticket = Ticket::findOrFail($id);

        // Ensure only admins can assign the ticket to staff
        if (!in_array($user->role, [1, 2]))
        { // Admin role ID
            return response()->json(['error' => 'Unauthorized to assign ticket.'], 403);
        }
    
        // ✅ Handle file upload BEFORE updating the ticket
        $photoPath = $request->hasFile('photo') 
            ? $request->file('photo')->store('photos', 'public') 
            : $ticket->photo; // Keep existing photo if no new file is uploaded
    
        $ticket->update([
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

        // Get Assigned Staff
        $assignedStaff = User::where('id', $request->assigned_by)->first();

        // Create Ticket Notification
        TicketNotification::create([
            'notified_to' => $assignedStaff->account->id,
            'notified_by' => Auth::id(),
            'message' => 'you assigned ' . $assignedStaff->account->full_name. ' to ticket ' . $ticket->ticket_order,
            'data' => json_encode([
                'module_type' => get_class($ticket),
                'module_id' => $ticket->id,
            'is_read' => 0, 
            'created_by' => Auth::id()
            ])
        ]);
    
        return response()->json(['message' => 'Ticket updated successfully', 'ticket' => $ticket], 200);
    }  

    // Update a ticket
    public function staffUpdate(Request $request, $id)
    {
        $user = $request->user(); // Get authenticated user

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        $ticket = Ticket::findOrFail($id);

        // Ensure only admins can assign the ticket to staff
        if (!in_array($user->role, [1, 2]))
        { // Admin role ID
            return response()->json(['error' => 'Unauthorized to assign ticket.'], 403);
        }
    
        // ✅ Handle file upload BEFORE updating the ticket
        $photoPath = $request->hasFile('photo') 
            ? $request->file('photo')->store('photos', 'public') 
            : $ticket->photo; // Keep existing photo if no new file is uploaded
    
        $ticket->update([
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

        // Get Admin Roles
        $adminRoleUsers = User::where('role', 1)->get();

        foreach ($adminRoleUsers as $adminRoleUser):

            // Create Ticket Notification
            TicketNotification::create([
                'notified_to' => $adminRoleUser->account->id,
                'notified_by' => Auth::id(),
                'message' => 'you updated ticket status to ' . $ticket->statusOptions[$ticket->status],
                'data' => json_encode([
                    'module_type' => get_class($ticket),
                    'module_id' => $ticket->id,
                'is_read' => 0,
                'created_by' => Auth::id()
                ])
            ]);
        endforeach;

        if ($ticket->account_id):

            // Get Ticket Creator
            $ticketCreator = $ticket->ticketCreator;

            // Create Ticket Notification
            TicketNotification::create([
                'notified_to' => $ticketCreator->id,
                'notified_by' => Auth::id(),
                'message' => ' updated ticket status to ' . $ticket->statusOptions[$ticket->status],
                'data' => json_encode([
                    'module_type' => get_class($ticket),
                    'module_id' => $ticket->id,
                'is_read' => 0,
                'created_by' => Auth::id()
                ])
            ]);

        endif;
    
        return response()->json(['message' => 'Ticket updated successfully', 'ticket' => $ticket], 200);
    }  
    

    public function storePhoto($photo)
    {
        return $photo->store('photos', 'public');
    }

    // Delete a ticket
    public function destroy($id)
    {
        $user = Auth::user();

        $ticket = Ticket::findOrFail($id);

        if ($ticket->account_id):

            // Get Ticket Creator
            $ticketCreator = $ticket->ticketCreator;

            // Create Ticket Notification
            TicketNotification::create([
                'notified_to' => $ticketCreator->id,
                'notified_by' => Auth::id(),
                'message' => ' deleted ticket ' . $ticket->ticket_order,
                'data' => json_encode([
                    'module_type' => get_class($ticket),
                    'module_id' => $ticket->id,
                'is_read' => 0,
                'created_by' => Auth::id()
                ])
            ]);

        endif;

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

    public function getAssignedTickets(Request $request)
    {
        $user = $request->user(); // Get authenticated user

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Ensure the user is a staff member and can view their assigned tickets
        if ($user->role != 2) { // Assuming role 2 is for Staff
            return response()->json(['error' => 'Unauthorized to view assigned tickets.'], 403);
        }

        // Get tickets assigned to this staff member
        $tickets = Ticket::where('assigned_by', $user->id)->get();

        return response()->json($tickets);
    }

    public function pendingStatus(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->approval_status == 1) return abort(403, 'Ticket is already in pending status.');

        $ticket->update([
            'approval_status' => 1,
            'approved_by' => null,
            'approved_date'=> null,
            'updated_by' => Auth::id(),
        ]);

        if ($ticket->account_id):

            // Get Ticket Creator
            $ticketCreator = $ticket->ticketCreator;

            // Create Ticket Notification
            TicketNotification::create([
                'notified_to' => $ticketCreator->id,
                'notified_by' => Auth::id(),
                'message' => ' you re-assigned the status of ticket ' .  $ticket->ticket_order,
                'data' => json_encode([
                    'module_type' => get_class($ticket),
                    'module_id' => $ticket->id,
                'is_read' => 0,
                'created_by' => Auth::id()
                ])
            ]);

        endif;

        return Ticket::find($id);
    }

    public function approveStatus(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $user = $request->user();

        if ($ticket->approval_status == 2) return abort(403, 'Ticket is already approved.');

        $ticket->update([
            'approval_status' => 2,
            'approved_by' => Auth::id(),
            'approved_date'=> now()->format('Y-m-d'),
            'updated_by' => Auth::id(),
        ]);

        if ($ticket->account_id):

            // Get Ticket Creator
            $ticketCreator = $ticket->ticketCreator;

            // Create Ticket Notification
            TicketNotification::create([
                'notified_to' => $ticketCreator->id,
                'notified_by' => Auth::id(),
                'message' => 'you approve the status of ticket ' . $ticket->ticket_order,
                'data' => json_encode([
                    'module_type' => get_class($ticket),
                    'module_id' => $ticket->id,
                'is_read' => 0,
                'created_by' => Auth::id()
                ])
            ]);

        endif;

        return Ticket::find($id);
    }

    public function cancelStatus(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->approval_status == 3) return abort(403, 'Ticket is already canceled.');

        $ticket->update([
            'approval_status' => 3,
            'approved_by' => null,
            'approved_date'=> null,
            'updated_by' => Auth::id(),
        ]);

        if ($ticket->account_id):

            // Get Ticket Creator
            $ticketCreator = $ticket->ticketCreator;

            // Create Ticket Notification
            TicketNotification::create([
                'notified_to' => $ticketCreator->id,
                'notified_by' => Auth::id(),
                'message' => 'you reject the status of ticket ' . $ticket->ticket_order,
                'data' => json_encode([
                    'module_type' => get_class($ticket),
                    'module_id' => $ticket->id,
                'is_read' => 0,
                'created_by' => Auth::id()
                ])
            ]);

        endif;

        return Ticket::find($id);
    }
    
};