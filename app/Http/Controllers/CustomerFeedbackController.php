<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use App\Models\CustomerFeedback;
use App\Models\TicketNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerFeedbackController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
    
        if ($user->role === 1) {
            $customerFeedback = CustomerFeedback::all();
        } elseif ($user->role === 2) {
            $customerFeedback = CustomerFeedback::where('assigned_by', $user->id)->get();
        } else {
            $customerFeedback = CustomerFeedback::where('account_id', $user->account->id)->get();
        }
    
        return response()->json($customerFeedback);
    } 

    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$user->account) {
            return response()->json(['error' => 'User has no account associated.'], 400);
        }

        // Validate incoming request data
        $request->validate([
            'rate' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        // Create the customer feedback
        $customerFeedback = CustomerFeedback::create([
            'account_id' => $user->account->id,
            'ticket_id' => $request->ticket_id,
            'ticket_order' => $request->ticket_order,
            'full_name' => $request->full_name,
            'assigned_by' => $request->assigned_by,
            'completed_date' => $request->completed_date,
            'completed_time' => $request->completed_time,
            'rate' => $request->rate, // ✅ FIXED HERE
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Customer Feedback submitted successfully',
            'customerFeedback' => $customerFeedback
        ], 201);
    }

    public function show($id)
    {
        // Fetch feedback by ID or return 404 if not found
        $customerFeedback = CustomerFeedback::findOrFail($id);
        return response()->json($customerFeedback);
    }

    public function getCustomerSatisfactionScore(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // For Admin (role == 1)
        if ($user->role == 1) {
            $total = CustomerFeedback::count();

            if ($total === 0) {
                return response()->json([
                    'positive' => 0,
                    'neutral' => 0,
                    'negative' => 0,
                ]);
            }

            $positive = CustomerFeedback::where('rate', 3)->count();
            $neutral  = CustomerFeedback::where('rate', 2)->count();
            $negative = CustomerFeedback::where('rate', 1)->count();
        }
        // For Staff (role == 2)
        else {
            $total = CustomerFeedback::where('assigned_by', $user->id)->count();

            if ($total === 0) {
                return response()->json([
                    'positive' => 0,
                    'neutral' => 0,
                    'negative' => 0,
                ]);
            }

            $positive = CustomerFeedback::where('assigned_by', $user->id)->where('rate', 3)->count();
            $neutral  = CustomerFeedback::where('assigned_by', $user->id)->where('rate', 2)->count();
            $negative = CustomerFeedback::where('assigned_by', $user->id)->where('rate', 1)->count();
        }

        return response()->json([
            'positive' => round(($positive / $total) * 100, 2),
            'neutral'  => round(($neutral / $total) * 100, 2),
            'negative' => round(($negative / $total) * 100, 2),
        ]);
    }
}
