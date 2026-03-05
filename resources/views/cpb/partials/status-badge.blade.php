@props(['status'])

@php
$statusConfig = [
// Proses berjalan: Background terang, border halus, teks gelap (Minimalis)
'rnd' => ['label' => 'RND', 'color' => 'light text-dark border', 'icon' => 'flask'],
'qa' => ['label' => 'QA Review', 'color' => 'light text-dark border', 'icon' => 'check-circle'],
'ppic' => ['label' => 'PPIC', 'color' => 'light text-dark border', 'icon' => 'calendar'],
'wh' => ['label' => 'Warehouse', 'color' => 'light text-dark border', 'icon' => 'warehouse'],
'produksi' => ['label' => 'Production', 'color' => 'light text-dark border', 'icon' => 'industry'],
'qc' => ['label' => 'QC', 'color' => 'light text-dark border', 'icon' => 'search'],

// Proses akhir/selesai: Warna hijau utama sebagai penanda sukses
'qa_final' => ['label' => 'QA Final', 'color' => 'success', 'icon' => 'check-double'],
'released' => ['label' => 'Released', 'color' => 'success', 'icon' => 'flag-checkered'],
];

$config = $statusConfig[$status] ?? ['label' => $status, 'color' => 'secondary', 'icon' => 'question-circle'];
@endphp

<span class="badge bg-{{ $config['color'] }} px-2 py-1 shadow-sm">
    <i class="fas fa-{{ $config['icon'] }} mr-1"></i> {{ $config['label'] }}
</span>