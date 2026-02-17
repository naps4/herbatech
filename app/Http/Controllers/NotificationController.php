<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('notifications.index', compact('notifications'));
    }
    
    public function show(Notification $notification)
    {
        // Ensure notification belongs to current user
        if ($notification->user_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }
        
        // Mark as read
        $notification->markAsRead();
        
        // Redirect based on notification type
        if ($notification->cpb_id) {
            return redirect()->route('cpb.show', $notification->cpb);
        }
        
        return redirect()->route('notifications.index');
    }
    
    public function markAsRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }
        
        $notification->markAsRead();
        
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->back()->with('success', 'Notifikasi telah ditandai sebagai dibaca.');
    }
    
    public function markAllAsRead(Request $request)
    {
        auth()->user()
            ->unreadNotifications()
            ->update(['is_read' => true]);
        
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->route('notifications.index')
            ->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }
    
    public function unreadCount()
    {
        $count = auth()->user()
            ->unreadNotifications()
            ->count();
        
        return response()->json(['count' => $count]);
    }
    
    public function clear(Request $request)
    {
        auth()->user()
            ->notifications()
            ->delete();
        
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->route('notifications.index')
            ->with('success', 'Semua notifikasi telah dihapus.');
    }
}