<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('account_id', Auth::id())
                                     ->orderBy('created_at', 'desc')
                                     ->get();
        return response()->json($notifications);
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
                                    ->where('account_id', Auth::id())
                                    ->firstOrFail();
        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function store()
    {
        $notification = Notification::create([
            'account_id' => $request->account_id,
            'title' => $request->title,
            'message' => $request->message,
            'is_read' => false
        ]);

        return response()->json($notification);
    }
}
