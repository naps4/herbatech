<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CPB;
use App\Models\Notification;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notifications = $user->unreadNotifications()->take(5)->get();
        
        // Get CPBs based on user role
        if ($user->isSuperAdmin() || $user->isQA()) {
            // Show all active CPBs
            $cpbs = CPB::where('status', '!=', 'released')
                ->orderBy('is_overdue', 'desc')
                ->orderBy('entered_current_status_at', 'asc')
                ->paginate(20);
        } else {
            // Show CPBs in user's department
            $cpbs = CPB::where('current_department_id', $user->id)
                ->orderBy('is_overdue', 'desc')
                ->orderBy('entered_current_status_at', 'asc')
                ->paginate(20);
        }
        
        // Statistics
        $stats = [
            'total_cpbs' => CPB::count(),
            'active_cpbs' => CPB::where('status', '!=', 'released')->count(),
            'overdue_cpbs' => CPB::where('is_overdue', true)->count(),
            'today_cpbs' => CPB::whereDate('created_at', Carbon::today())->count(),
        ];
        
        return view('dashboard.index', compact('cpbs', 'notifications', 'stats'));
    }
    
    public function notifications()
    {
        $user = auth()->user();
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('notifications.index', compact('notifications'));
    }
    
    public function markNotificationAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }
    
    public function markAllNotificationsAsRead()
    {
        auth()->user()->unreadNotifications()->update(['is_read' => true]);
        
        return response()->json(['success' => true]);
    }
}