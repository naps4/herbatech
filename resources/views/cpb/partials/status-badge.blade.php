@props(['status'])

@php
    $statusConfig = [
        'rnd' => ['label' => 'RND', 'color' => 'primary', 'icon' => 'flask'],
        'qa' => ['label' => 'QA Review', 'color' => 'info', 'icon' => 'check-circle'],
        'ppic' => ['label' => 'PPIC', 'color' => 'secondary', 'icon' => 'calendar'],
        'wh' => ['label' => 'Warehouse', 'color' => 'dark', 'icon' => 'warehouse'],
        'produksi' => ['label' => 'Production', 'color' => 'warning', 'icon' => 'industry'],
        'qc' => ['label' => 'QC', 'color' => 'info', 'icon' => 'search'],
        'qa_final' => ['label' => 'QA Final', 'color' => 'success', 'icon' => 'check-double'],
        'released' => ['label' => 'Released', 'color' => 'success', 'icon' => 'flag-checkered'],
    ];
    
    $config = $statusConfig[$status] ?? ['label' => $status, 'color' => 'secondary', 'icon' => 'question-circle'];
@endphp

<span class="badge bg-{{ $config['color'] }}">
    <i class="fas fa-{{ $config['icon'] }} mr-1"></i> {{ $config['label'] }}
</span>