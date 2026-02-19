<?php
// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CPB;
use App\Models\HandoverLog;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CPBExport;
use App\Exports\PerformanceExport;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:superadmin,qa');
    }
    
    public function index(Request $request)
    {
        $query = CPB::query();
        
        // Date filters
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        // Type filter
        if ($request->has('type') && $request->type != 'all') {
            $query->where('type', $request->type);
        }
        
        // Status filter
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        // Overdue filter
        if ($request->has('overdue') && $request->overdue != 'all') {
            $query->where('is_overdue', $request->overdue == 'yes');
        }
        
        $cpbs = $query->with(['creator', 'currentDepartment'])
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        
        // Statistics
        $total = CPB::count();
        $overdue = CPB::where('is_overdue', true)->count();
        $released = CPB::where('status', 'released')->count();
        $averageDuration = HandoverLog::avg('duration_in_hours') ?? 0;
        
        return view('reports.index', compact('cpbs', 'total', 'overdue', 'released', 'averageDuration'));
    }
    
public function export(Request $request)
    {
        try {
            // Cek jika class CPBExport ada
            if (!class_exists('App\Exports\CPBExport')) {
                // Jika tidak ada, gunakan simple export
                return $this->simpleExport($request);
            }
            
            // Jika ada, gunakan CPBExport
            $fileName = 'cpb-report-' . date('Y-m-d-H-i-s') . '.xlsx';
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\CPBExport($request), 
                $fileName
            );
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error export: ' . $e->getMessage());
        }
    }

    private function simpleExport(Request $request)
    {
        $query = CPB::query();
        
        // Apply filters
        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        $cpbs = $query->with(['creator', 'currentDepartment'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Generate CSV secara manual
        $fileName = 'cpb-report-' . date('Y-m-d-H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];
        
        $callback = function() use ($cpbs) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'No. Batch', 'Jenis', 'Produk', 'Status', 'Lokasi',
                'Durasi Produksi', 'Durasi di Status', 'Overdue',
                'Dibuat Oleh', 'Tanggal Dibuat'
            ]);
            
            // Data
            foreach ($cpbs as $cpb) {
                fputcsv($file, [
                    $cpb->batch_number,
                    $cpb->type == 'pengolahan' ? 'Pengolahan' : 'Pengemasan',
                    $cpb->product_name,
                    $cpb->status,
                    $cpb->currentDepartment ? $cpb->currentDepartment->name : '-',
                    $cpb->schedule_duration . ' jam',
                    $cpb->duration_in_current_status . ' jam',
                    $cpb->is_overdue ? 'Ya' : 'Tidak',
                    $cpb->creator ? $cpb->creator->name : '-',
                    $cpb->created_at->format('d/m/Y H:i')
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    


    public function audit(Request $request)
    {
        $query = HandoverLog::query();
        
        // Date filters
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('handed_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('handed_at', '<=', $request->end_date);
        }
        
        // User filter
        if ($request->has('user_id') && $request->user_id) {
            $query->where(function($q) use ($request) {
                $q->where('handed_by', $request->user_id)
                  ->orWhere('received_by', $request->user_id);
            });
        }
        
        // CPB filter
        if ($request->has('batch_number')) {
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
        // 1. Gunakan satu query dasar untuk semua hitungan agar sinkron
        $baseQuery = \App\Models\HandoverLog::query();
        
        // 2. Apply Filters (Tanggal & Departemen)
        if ($request->filled('start_date')) {
            $baseQuery->whereDate('handed_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $baseQuery->whereDate('handed_at', '<=', $request->end_date);
        }
        if ($request->filled('department')) {
            $baseQuery->where('from_status', $request->department);
        }
    
        // 3. Get department performance (untuk Grafik & Tabel Detail)
        $performance = (clone $baseQuery)
        ->where('from_status', '!=', 'created') 
        ->selectRaw('
            from_status,
            COUNT(*) as total_handovers,
            AVG(duration_in_hours) as avg_duration,
            SUM(CASE WHEN was_overdue = 1 THEN 1 ELSE 0 END) as overdue_count,
            (SUM(CASE WHEN was_overdue = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100) as overdue_percentage
        ')
        ->groupBy('from_status')
        ->get();
        
        // 4. Get user performance (Top Performers)
        // Cukup gunakan with('sender') dan groupBy, tidak perlu map manual lagi
        $userPerformance = (clone $baseQuery)
            ->with('sender')
            ->selectRaw('
                handed_by,
                COUNT(*) as total_handovers_count,
                AVG(duration_in_hours) as avg_duration,
                SUM(CASE WHEN was_overdue = 1 THEN 1 ELSE 0 END) as overdue_count
            ')
            ->groupBy('handed_by')
            ->get()
            ->sortByDesc('total_handovers_count')
            ->values();
        
        // 5. Summary statistics (untuk Box Kecil Biru/Hijau/Merah)
        $summary = [
            'total_handovers' => $baseQuery->count(),
            'avg_duration' => $baseQuery->avg('duration_in_hours') ?? 0,
            'overdue_count' => $baseQuery->where('was_overdue', true)->count(),
        ];
        
        // AJAX handling
        if ($request->ajax() || $request->has('ajax')) {
            return response()->json([
                'performance' => $performance,
                'userPerformance' => $userPerformance,
                'summary' => $summary
            ]);
        }
        
        // Export handling
        if ($request->has('export')) {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new PerformanceExport($performance, $userPerformance), 
                'performance-report-' . date('Y-m-d') . '.xlsx'
            );
        }
        
        return view('reports.performance', compact('performance', 'userPerformance', 'summary'));
    }
}