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
        /** @var \App\Models\User $user*/    
        $user = auth()->user();
        
        $baseQuery = \App\Models\CPB::query();

        if (!$user->isSuperAdmin() && !$user->isQA()) {
            $baseQuery->where(function ($q) use ($user) {
                $q->where('status', $user->role)
                ->orWhere('created_by', $user->id)
                ->orWhereHas('handoverLogs', function ($sub) use ($user) {
                    $sub->where('from_status', $user->role)
                        ->orWhere('to_status', $user->role);
                });
            });
        }

        // 1. Hitung Statistik (Tetap sama)
        $stats = [
            'total_cpbs'   => (clone $baseQuery)->count(),
            'active_cpbs'  => (clone $baseQuery)->where('status', '!=', 'released')->count(),
            'overdue_cpbs' => (clone $baseQuery)
            ->where('is_overdue', true)
            ->where('status', '!=', 'released')
            ->count(),
            'today_cpbs'   => (clone $baseQuery)->where(function($q) {
                $q->whereDate('created_at', \Carbon\Carbon::today())
                ->orWhereHas('handoverLogs', function($query) {
                    $query->whereDate('created_at', \Carbon\Carbon::today());
                });
            })->count(),
        ];

        // 2. PERBAIKAN: Gunakan paginate() bukan get() agar bisa menggunakan links() dan appends()
        $cpbs = (clone $baseQuery)
            ->with(['currentDepartment'])
            ->where('status', '!=', 'released')
            ->latest('entered_current_status_at')
            ->paginate(10); // Menampilkan 10 data per halaman dengan fitur pagination

        return view('dashboard.index', compact('cpbs', 'stats'));
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
    public function exportPdf(CPB $cpb)
{
    // Logika untuk generate PDF
    $pdf = PDF::loadView('cpb.export-pdf', compact('cpb'));
    return $pdf->download('CPB-'.$cpb->batch_number.'.pdf');
}
}