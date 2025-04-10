<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            'department' => 'required',
            'subject' => 'required',
            'priority_level' => 'required',
            'description' => 'nullable',
            'request_date' => 'required',
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
            'assigned_by' => 0,
            'request_date' => $request->request_date,
            'completed_date' => $request->completed_date,
            'completed_time' => $request->completed_time,
        ]);

        // Get Admin Roles
        $adminRoleUsers = User::where('role', 1)->get();

        foreach ($adminRoleUsers as $adminRoleUser):

            // Create Ticket Notification
            TicketNotification::create([
                'notified_to' => $adminRoleUser->account->id,
                'notified_by' => Auth::user()->account->id,
                'message' => $ticket->full_name  . ' has created a new ticket ' . $ticket->ticket_order,
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

    public function storeWalkin(Request $request)
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
            'assigned_by' => 0,
            'request_date' => $request->request_date,
            'completed_date' => $request->completed_date,
            'completed_time' => $request->completed_time,
        ]);

        // Get Admin Roles
        $adminRoleUsers = User::where('role', 1)->get();

        foreach ($adminRoleUsers as $adminRoleUser):

            // Create Ticket Notification
            TicketNotification::create([
                'notified_to' => $adminRoleUser->account->id,
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

    
        $ticket->update([
            'full_name' => $request->full_name,
            'department' => $request->department,
            'subject' => $request->subject,
            'priority_level' => $request->priority_level,
            'status' => $request->status,
            'description' => $request->description,
            'assigned_by' => $request->assigned_by,
            'request_date' => $request->request_date,
            'completed_date' => $request->completed_date,
            'completed_time' => $request->completed_time,
        ]);

        // Get Assigned Staff
        $assignedStaff = User::where('id', $request->assigned_by)->first();

        // Create Ticket Notification
        TicketNotification::create([
            'notified_to' => $assignedStaff->account->id,
            'notified_by' => Auth::id(),
            'message' => ' you have been assigned to ' . $ticket->ticket_order . ' by the admin. ',
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

    
        $ticket->update([
            'full_name' => $request->full_name,
            'department' => $request->department,
            'subject' => $request->subject,
            'priority_level' => $request->priority_level,
            'status' => $request->status,
            'description' => $request->description,
            'assigned_by' => $request->assigned_by,
            'request_date' => $request->request_date,
            'completed_date' => $request->completed_date,
            'completed_time' => $request->completed_time,
        ]);

        // Get Admin Roles
        $adminRoleUsers = User::where('role', 1)->get();

        foreach ($adminRoleUsers as $adminRoleUser):

            // Create Ticket Notification
            TicketNotification::create([
                'notified_to' => $adminRoleUser->account->id,
                'notified_by' => Auth::id(),
                'message' => 'you updated ticket',
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
                'message' => 'staff updated your ticket status ' . $ticket->ticket_order,
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

    public function getPriority()
    {
        $low = Ticket::where('priority_level', 1)->count(); // status 3 = Resolved
        $medium = Ticket::where('priority_level', 2)->count(); // status 4 = Unresolved
        $high = Ticket::where('priority_level', 3)->count(); // status 4 = Unresolved
        $emergency = Ticket::where('priority_level', 4)->count(); // status 4 = Unresolved

        return response()->json([
            'low' => $low,
            'medium' => $medium,
            'high' => $high,
            'emergency' => $emergency
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
                'message' => 'admin approve the status of ticket '  . $ticket->ticket_order,
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

    public function getMonthlySubmittedTickets(Request $request)
    {
        $year = $request->input('year', now()->year);
    
        $monthlyData = [];
    
        for ($month = 1; $month <= 12; $month++) {
            $monthlyCount = Ticket::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();
    
            $monthlyData[] = $monthlyCount;
        }
    
        return response()->json([
            'monthly' => $monthlyData,
            'year' => $year,
        ]);
    }
    
};