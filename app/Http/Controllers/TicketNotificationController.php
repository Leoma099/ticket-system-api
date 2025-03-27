<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TicketNotification;

class TicketNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $account = $user->account;

        return $account->loggedTicketNotifications()->where('is_read', 0)->orderBy('created_at', 'desc')->paginate(10);
    }

    public function unreadCount(Request $request)
    {
        $user = Auth::user();
        
        $account = $user->account;

        return $account->loggedTicketNotifications()->where('is_read', 0)->count();
    }

    public function markAsRead(Request $request, $id)
    {
        $ticketNotification = TicketNotification::findOrFail($id);

        $ticketNotification->update([
            'is_read' => 1,
            'updated_by' => Auth::id()
        ]);

        return TicketNotification::find($ticketNotification->id);
    }
}
