<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CPB;
use App\Models\HandoverLog;
use App\Models\User;
use Carbon\Carbon;
use App\Exports\CPBExport;
use App\Exports\PerformanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = CPB::query()->with(['creator', 'currentDepartment']);

        // --- LOGIKA VISIBILITAS (Best Practice) ---
        // Jika bukan SuperAdmin/QA, batasi data yang pernah dilewati atau sedang dipegang
        if (!$user->isSuperAdmin() && !$user->isQA()) {
            $query->where(function($q) use ($user) {
                $q->where('status', $user->role)
                  ->orWhere('created_by', $user->id)
                  ->orWhereHas('handoverLogs', function($sub) use ($user) {
                      $sub->where('from_status', $user->role)
                          ->orWhere('to_status', $user->role);
                  });
            });
        }
        
        // Filter Tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        // Filter Metadata
        if ($request->filled('type') && $request->type != 'all') {
            $query->where('type', $request->type);
        }
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('batch_number')) {
            $query->where('batch_number', 'like', '%' . $request->batch_number . '%');
        }
        
        $cpbs = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        
        // Statistik disesuaikan dengan hasil query filter (Visibility-Aware)
        $total = (clone $query)->count();
        $overdue = (clone $query)->where('is_overdue', true)->count();
        $released = (clone $query)->where('status', 'released')->count();
        
        return view('reports.index', compact('cpbs', 'total', 'overdue', 'released'));
    }

    public function audit(Request $request)
    {
        $user = auth()->user();
        $query = HandoverLog::query()->with(['cpb', 'sender', 'receiver']);
        
        // --- Proteksi Audit Trail ---
        if (!$user->isSuperAdmin() && !$user->isQA()) {
            $query->where(function($q) use ($user) {
                $q->where('from_status', $user->role)
                  ->orWhere('to_status', $user->role);
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('handed_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('handed_at', '<=', $request->end_date);
        }
        if ($request->filled('batch_number')) {
            $query->whereHas('cpb', function($q) use ($request) {
                $q->where('batch_number', 'like', '%' . $request->batch_number . '%');
            });
        }
        
        $handovers = $query->orderBy('handed_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        
        $users = User::orderBy('name')->get();
        
        return view('reports.audit', compact('handovers', 'users'));
    }

    public function performance(Request $request)
    {
        $user = auth()->user();
        
        // Hanya QA/Admin yang boleh melihat analisis performa keseluruhan
        if (!$user->isSuperAdmin() && !$user->isQA()) {
            return redirect()->route('reports.index')->with('error', 'Akses Terbatas: Hanya QA Team yang dapat mengakses analisis performa.');
        }

        $baseQuery = HandoverLog::query();
        
        if ($request->filled('start_date')) {
            $baseQuery->whereDate('handed_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $baseQuery->whereDate('handed_at', '<=', $request->end_date);
        }

        // 1. Data Per Departemen (from_status mewakili departemen pengirim)
        $performance = (clone $baseQuery)
            ->whereNotIn('from_status', ['created', 'released']) 
            ->selectRaw('
                from_status,
                COUNT(*) as total_handovers,
                AVG(duration_in_hours) as avg_duration,
                MIN(duration_in_hours) as min_duration,
                MAX(duration_in_hours) as max_duration,
                SUM(CASE WHEN was_overdue = 1 THEN 1 ELSE 0 END) as overdue_count
            ')
            ->groupBy('from_status')
            ->get();

        // 2. Top Performers (Per User)
        $userPerformance = (clone $baseQuery)
            ->selectRaw('handed_by, COUNT(*) as total_handovers_count, AVG(duration_in_hours) as avg_duration')
            ->with('sender')
            ->groupBy('handed_by')
            ->orderBy('total_handovers_count', 'desc')
            ->take(15)
            ->get();
        
        // 3. Ringkasan
        $summary = [
            'total_handovers' => $baseQuery->count(),
            'avg_duration' => $baseQuery->avg('duration_in_hours') ?? 0,
            'overdue_count' => (clone $baseQuery)->where('was_overdue', true)->count(),
        ];

        // 4. Export logic
        if ($request->has('export')) {
            return Excel::download(
                new PerformanceExport($performance, $userPerformance),
                'performance-report-' . date('Y-m-d') . '.xlsx'
            );
        }
        
        return view('reports.performance', compact('performance', 'userPerformance', 'summary'));
    }

    public function export(Request $request)
    {
        // Proteksi Export hanya untuk QA/Admin
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isQA()) {
            abort(403);
        }

        $fileName = 'cpb-report-' . date('Y-m-d') . '.xlsx';
        return Excel::download(new CPBExport($request), $fileName);
    }
}