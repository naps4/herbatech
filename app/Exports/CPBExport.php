<?php

namespace App\Exports;

use App\Models\CPB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CPBExport implements FromCollection, WithHeadings
{
    protected $request;
    
    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }
    
    public function collection()
    {
        $query = CPB::with(['creator', 'currentDepartment']);
        
        if ($this->request) {
            // Filter berdasarkan tanggal
            if ($this->request->start_date) {
                $query->whereDate('created_at', '>=', $this->request->start_date);
            }
            if ($this->request->end_date) {
                $query->whereDate('created_at', '<=', $this->request->end_date);
            }
            
            // Filter lainnya
            if ($this->request->type && $this->request->type != 'all') {
                $query->where('type', $this->request->type);
            }
            if ($this->request->status && $this->request->status != 'all') {
                $query->where('status', $this->request->status);
            }
            if ($this->request->overdue && $this->request->overdue != 'all') {
                $query->where('is_overdue', $this->request->overdue == 'yes');
            }
        }
        
        return $query->orderBy('created_at', 'desc')->get()->map(function($cpb) {
            return [
                'No. Batch' => $cpb->batch_number,
                'Jenis' => $cpb->type == 'pengolahan' ? 'Pengolahan' : 'Pengemasan',
                'Produk' => $cpb->product_name,
                'Status' => $this->getStatusText($cpb->status),
                'Lokasi' => $cpb->currentDepartment ? $cpb->currentDepartment->name : '-',
                'Durasi Produksi' => $cpb->schedule_duration . ' jam',
                'Durasi di Status' => $cpb->duration_in_current_status . ' jam',
                'Batas Waktu' => $cpb->time_limit . ' jam',
                'Status Waktu' => $cpb->is_overdue ? 'OVERDUE' : ($cpb->duration_in_current_status > $cpb->time_limit * 0.8 ? 'WARNING' : 'OK'),
                'Dibuat Oleh' => $cpb->creator ? $cpb->creator->name : '-',
                'Overdue' => $cpb->is_overdue ? 'Ya' : 'Tidak',
                'Tanggal Dibuat' => $cpb->created_at->format('d/m/Y H:i'),
                'Terakhir Update' => $cpb->updated_at->format('d/m/Y H:i'),
            ];
        });
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
        
        return $statuses[$status] ?? $status;
    }
}