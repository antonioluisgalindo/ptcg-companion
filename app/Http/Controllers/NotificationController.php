<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(20);
        Auth::user()->unreadNotifications()->update(['is_read' => true, 'read_at' => now()]);
        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Notification $notification)
    {
        abort_if($notification->user_id !== Auth::id(), 403);
        $notification->markAsRead();
        return back();
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications()->update(['is_read' => true, 'read_at' => now()]);
        return back()->with('success', 'Todas las notificaciones marcadas como leídas.');
    }

    public function destroy(Notification $notification)
    {
        abort_if($notification->user_id !== Auth::id(), 403);
        $notification->delete();
        return back();
    }
}
