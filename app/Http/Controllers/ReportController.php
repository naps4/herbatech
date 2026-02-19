<?php
// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CPB;
use App\Models\HandoverLog;
use App\Models\User;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        // Middleware role hanya membatasi index, export, dan performance.
        // Fungsi 'audit' dikecualikan agar role RND, PPIC, dll bisa akses.
        $this->middleware('role:superadmin,qa')->except(['audit']);
    }
    
    public function index(Request $request)
    {
        $query = CPB::query();
        
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->has('type') && $request->type != 'all') {
            $query->where('type', $request->type);
        }
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        $cpbs = $query->with(['creator', 'currentDepartment'])
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        
        $total = CPB::count();
        $overdue = CPB::where('is_overdue', true)->count();
        $released = CPB::where('status', 'released')->count();
        $averageDuration = HandoverLog::avg('duration_in_hours') ?? 0;
        
        return view('reports.index', compact('cpbs', 'total', 'overdue', 'released', 'averageDuration'));
    }

    public function audit(Request $request)
    {
        $query = HandoverLog::query();
        
        if ($request->filled('start_date')) {
            $query->whereDate('handed_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('handed_at', '<=', $request->end_date);
        }
        if ($request->filled('user_id')) {
            $query->where(function($q) use ($request) {
                $q->where('handed_by', $request->user_id)
                  ->orWhere('received_by', $request->user_id);
            });
        }
        if ($request->filled('batch_number')) {
            $query->whereHas('cpb', function($q) use ($request) {
                $q->where('batch_number', 'like', '%' . $request->batch_number . '%');
            });
        }
        
        $handovers = $query->with(['cpb', 'sender', 'receiver'])
            ->orderBy('handed_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        
        $users = User::orderBy('name')->get();
        
        return view('reports.audit', compact('handovers', 'users'));
    }

    public function performance(Request $request)
    {
        $baseQuery = HandoverLog::query();
        
        if ($request->filled('start_date')) {
            $baseQuery->whereDate('handed_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $baseQuery->whereDate('handed_at', '<=', $request->end_date);
        }

        $performance = (clone $baseQuery)
            ->where('from_status', '!=', 'created') 
            ->selectRaw('
                from_status,
                COUNT(*) as total_handovers,
                AVG(duration_in_hours) as avg_duration,
                SUM(CASE WHEN was_overdue = 1 THEN 1 ELSE 0 END) as overdue_count
            ')
            ->groupBy('from_status')
            ->get();
        
        $summary = [
            'total_handovers' => $baseQuery->count(),
            'avg_duration' => $baseQuery->avg('duration_in_hours') ?? 0,
            'overdue_count' => $baseQuery->where('was_overdue', true)->count(),
        ];
        
        return view('reports.performance', compact('performance', 'summary'));
    }

    public function export(Request $request)
    {
        $fileName = 'cpb-report-' . date('Y-m-d') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\CPBExport($request), $fileName);
    }
}