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
        
        // Siapkan query dasar untuk statistik agar sinkron dengan role
        $statsQuery = \App\Models\CPB::query();
        
        // Role-based filtering untuk Daftar CPB dan Statistik
        if (!$user->isSuperAdmin() && !$user->isQA()) {
            $statsQuery->where(function($q) use ($user) {
                $q->where('status', $user->role) // Berdasarkan departemen/role saat ini
                  ->orWhere('created_by', $user->id); // Atau yang dibuat sendiri
            });
        }
    
        // Ambil data CPB untuk tabel (menggunakan query yang sudah difilter role)
        $cpbs = (clone $statsQuery)->where('status', '!=', 'released')
            ->orderBy('is_overdue', 'desc')
            ->orderBy('entered_current_status_at', 'asc')
            ->paginate(20);
        
        // Hitung Statistik berdasarkan query yang sudah difilter role
        $stats = [
            'total_cpbs'   => (clone $statsQuery)->count(),
            'active_cpbs'  => (clone $statsQuery)->where('status', '!=', 'released')->count(),
            'overdue_cpbs' => (clone $statsQuery)->where('is_overdue', true)->count(),
            'today_cpbs'   => (clone $statsQuery)->whereDate('created_at', \Carbon\Carbon::today())->count(),
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