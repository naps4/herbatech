<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Tambahkan ini agar lebar kolom otomatis
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PerformanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
            strtoupper($dept->from_status), // Menggunakan uppercase agar lebih rapi (RND, QA, dll)
            $dept->total_handovers,
            round($dept->avg_duration, 2), // Menggunakan 2 desimal agar durasi singkat terlihat (misal 0.25)
            $dept->min_duration !== null ? round($dept->min_duration, 2) : '-',
            $dept->max_duration !== null ? round($dept->max_duration, 2) : '-',
            $dept->overdue_count,
            $overduePercentage . '%',
        ];
    }
    
    public function styles(Worksheet $sheet)
    {
        // Memberikan border dan styling pada header
        $sheet->getStyle('A1:G1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFEBEDEF');

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}