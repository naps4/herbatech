<?php

namespace App\Exports;

use App\Models\CPB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CPBExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;
    
    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }
    
    public function collection()
    {
        $user = auth()->user();
        $query = CPB::with(['creator', 'currentDepartment']);
        
        // 1. TERAPKAN FILTER VISIBILITAS ROLE
        if (!$user->isSuperAdmin() && (!$user->isQA() || $user->role !== 'qa') && $user->role !== 'rnd') {
            $query->where(function ($q) use ($user) {
                $q->where('status', $user->role) 
                    ->orWhere('created_by', $user->id)
                    ->orWhereHas('handoverLogs', function ($subQuery) use ($user) {
                        $subQuery->where('from_status', $user->role) 
                            ->orWhere('to_status', $user->role);
                    });
            });
        }
        
        if ($this->request) {
            // 2. Filter berdasarkan tanggal
            if ($this->request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $this->request->start_date);
            }
            if ($this->request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $this->request->end_date);
            }
            
            // 3. Filter berdasarkan type
            if ($this->request->filled('type') && $this->request->type != 'all') {
                // PERBAIKAN DI SINI: Jika type = active, ambil yang belum di-released
                if ($this->request->type === 'active') {
                    $query->where('status', '!=', 'released');
                } else {
                    $query->where('type', $this->request->type);
                }
            }

            // 4. Filter berdasarkan status
            if ($this->request->filled('status') && $this->request->status != 'all') {
                $query->where('status', $this->request->status);
            }
            
            // 5. Filter Overdue
            if ($this->request->filled('overdue') && $this->request->overdue !== 'all') {
                $isOverdue = in_array($this->request->overdue, ['yes', 'true', '1']);
                $query->where('is_overdue', $isOverdue);
            }
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }
    
    public function map($cpb): array
    {
        return [
            $cpb->batch_number,
            $cpb->type == 'pengolahan' ? 'Pengolahan' : 'Pengemasan',
            $cpb->product_name,
            $this->getStatusText($cpb->status),
            $cpb->currentDepartment ? $cpb->currentDepartment->name : '-',
            $cpb->schedule_duration . ' jam',
            $cpb->duration_in_current_status . ' jam',
            $cpb->time_limit . ' jam',
            $cpb->is_overdue ? 'OVERDUE' : ($cpb->duration_in_current_status > $cpb->time_limit * 0.8 ? 'WARNING' : 'OK'),
            $cpb->creator ? $cpb->creator->name : '-',
            $cpb->is_overdue ? 'Ya' : 'Tidak',
            $cpb->created_at ? $cpb->created_at->format('d/m/Y H:i') : '-',
            $cpb->updated_at ? $cpb->updated_at->format('d/m/Y H:i') : '-',
        ];
    }
    
    public function headings(): array
    {
        return [
            'No. Batch',
            'Jenis',
            'Produk',
            'Status',
            'Lokasi',
            'Durasi Produksi',
            'Durasi di Status',
            'Batas Waktu',
            'Status Waktu',
            'Dibuat Oleh',
            'Overdue',
            'Tanggal Dibuat',
            'Terakhir Update'
        ];
    }
    
    private function getStatusText($status)
    {
        $statuses = [
            'rnd' => 'RND',
            'qa' => 'QA Review',
            'ppic' => 'PPIC',
            'wh' => 'Warehouse',
            'produksi' => 'Produksi',
            'qc' => 'QC',
            'qa_final' => 'QA Final',
            'released' => 'Released'
        ];
        
        return $statuses[$status] ?? strtoupper($status);
    }
}