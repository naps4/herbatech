<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PerformanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $performance;
    protected $userPerformance;
    
    public function __construct($performance, $userPerformance)
    {
        $this->performance = $performance;
        $this->userPerformance = $userPerformance;
    }
    
    public function collection()
    {
        return collect($this->performance);
    }
    
    public function headings(): array
    {
        return [
            'Departemen',
            'Jumlah Handover',
            'Rata-rata Durasi (jam)',
            'Durasi Tercepat (jam)',
            'Durasi Terlama (jam)',
            'Jumlah Overdue',
            '% Overdue',
        ];
    }
    
    public function map($dept): array
    {
        $overduePercentage = $dept->total_handovers > 0 
            ? round(($dept->overdue_count / $dept->total_handovers) * 100, 1) 
            : 0;
            
        return [
            ucfirst($dept->from_status),
            $dept->total_handovers,
            round($dept->avg_duration, 1),
            $dept->min_duration ?? '-',
            $dept->max_duration ?? '-',
            $dept->overdue_count,
            $overduePercentage . '%',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
            
            // Style for overdue percentage > 20%
            'G' => [
                'font' => [
                    'color' => ['argb' => 'FFFF0000'],
                ],
            ],
        ];
    }
}