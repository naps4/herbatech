@extends('layouts.app')

@section('title')
@section('page-title')
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter Performance</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.performance') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="start_date">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" 
                                       class="form-control" value="{{ request('start_date') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="end_date">Tanggal Akhir</label>
                                <input type="date" name="end_date" id="end_date" 
                                       class="form-control" value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="department">Departemen</label>
                                <select name="department" id="department" class="form-control">
                                    <option value="">Semua Departemen</option>
                                    @foreach(['rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc'] as $dept)
                                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ strtoupper($dept) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="metric">Metric</label>
                                <select name="metric" id="metric" class="form-control">
                                    <option value="duration" {{ request('metric') == 'duration' ? 'selected' : '' }}>Rata-rata Durasi</option>
                                    <option value="count" {{ request('metric') == 'count' ? 'selected' : '' }}>Jumlah CPB</option>
                                    <option value="overdue" {{ request('metric') == 'overdue' ? 'selected' : '' }}>Persentase Overdue</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('reports.performance') }}" class="btn btn-default">
                                <i class="fas fa-sync-alt"></i> Reset
                            </a>
                            <button type="button" class="btn btn-success float-right" onclick="exportPerformance()">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Performance per Departemen</h3></div>
            <div class="card-body">
                <canvas id="performanceChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $summary['total_handovers'] ?? 0 }}</h3>
                <p>Total Handover</p>
            </div>
            <div class="icon"><i class="fas fa-exchange-alt"></i></div>
        </div>
        
        <div class="small-box bg-success">
            <div class="inner">
                <h3>
                    @if(($summary['avg_duration'] ?? 0) < 1)
                        {{ round(($summary['avg_duration'] ?? 0) * 60) }}<sup style="font-size: 20px">m</sup>
                    @else
                        {{ round($summary['avg_duration'] ?? 0, 1) }}<sup style="font-size: 20px">j</sup>
                    @endif
                </h3>
                <p>Rata-rata Durasi</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
        
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $summary['overdue_count'] ?? 0 }}</h3>
                <p>Total Overdue</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Detail Performance per Departemen</h3></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Departemen</th>
                                <th>Jumlah CPB</th>
                                <th>Avg Durasi</th>
                                <th>Tercepat</th>
                                <th>Terlama</th>
                                <th>Overdue</th>
                                <th>Overdue Rate</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($performance as $dept)
                                <tr>
                                    <td><strong>{{ strtoupper($dept->from_status) }}</strong></td>
                                    <td class="text-right">{{ $dept->total_handovers }}</td>
                                    <td class="text-right">
                                        @if($dept->avg_duration < 1) {{ round($dept->avg_duration * 60) }} m @else {{ round($dept->avg_duration, 1) }} j @endif
                                    </td>
                                    <td class="text-right">
                                        @if($dept->min_duration < 1) {{ round($dept->min_duration * 60) }} m @else {{ round($dept->min_duration, 1) }} j @endif
                                    </td>
                                    <td class="text-right">
                                        @if($dept->max_duration < 1) {{ round($dept->max_duration * 60) }} m @else {{ round($dept->max_duration, 1) }} j @endif
                                    </td>
                                    <td class="text-right text-danger">{{ $dept->overdue_count }}</td>
                                    <td class="text-right">
                                        @php $overdueRate = $dept->overdue_percentage ?? 0; @endphp
                                        <span class="badge {{ $overdueRate > 20 ? 'bg-danger' : ($overdueRate > 10 ? 'bg-warning' : 'bg-success') }}">
                                            {{ round($overdueRate, 1) }}%
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        @php $score = calculatePerformanceScore($dept); @endphp
                                        <span class="badge {{ $score >= 80 ? 'bg-success' : ($score >= 60 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $score }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Top Performers</h3></div>
            <div class="card-body">
                <div class="row">
                    @foreach($userPerformance->take(3) as $userPerf)
                    <div class="col-md-4">
                        <div class="card card-widget widget-user">
                            <div class="widget-user-header bg-info">
                                <h3 class="widget-user-username">{{ $userPerf->sender->name ?? 'User' }}</h3>
                                <h5 class="widget-user-desc">{{ strtoupper($userPerf->sender->role ?? 'Staff') }}</h5>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header">{{ $userPerf->total_handovers_count }}</h5>
                                            <span class="description-text">Handover</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header">
                                                @if($userPerf->avg_duration < 1) {{ round($userPerf->avg_duration * 60) }}m @else {{ round($userPerf->avg_duration, 1) }}j @endif
                                            </h5>
                                            <span class="description-text">Avg</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="description-block">
                                            @php $uScore = calculateUserPerformanceScore($userPerf); @endphp
                                            <h5 class="description-header">{{ $uScore }}%</h5>
                                            <span class="description-text">Score</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    const formatTimeLabel = (val) => {
        if (val === 0) return '0';
        return val < 1 ? Math.round(val * 60) + 'm' : val.toFixed(1) + 'j';
    };

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($performance->pluck('from_status')->map(fn($i) => strtoupper($i))) !!},
            datasets: [
                {
                    label: 'Rata-rata Durasi',
                    data: {!! json_encode($performance->pluck('avg_duration')) !!},
                    backgroundColor: 'rgba(0, 123, 255, 0.7)',
                    borderColor: '#007bff',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: '% Overdue',
                    data: {!! json_encode($performance->pluck('overdue_percentage')) !!},
                    borderColor: '#dc3545',
                    backgroundColor: '#dc3545',
                    type: 'line',
                    borderWidth: 3,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: { 
                    beginAtZero: true, 
                    title: { display: true, text: 'Waktu Pengerjaan' },
                    ticks: { callback: (v) => formatTimeLabel(v) } 
                },
                y1: { 
                    position: 'right', 
                    title: { display: true, text: 'Overdue %' }, 
                    min: 0, max: 100, 
                    grid: { drawOnChartArea: false },
                    ticks: { callback: (v) => v + '%' }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let val = context.parsed.y;
                            if (context.datasetIndex === 0) return label + ': ' + (val < 1 ? Math.round(val * 60) + ' Menit' : val.toFixed(1) + ' Jam');
                            return label + ': ' + val + '%';
                        }
                    }
                }
            }
        }
    });
});

function exportPerformance() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'true');
    window.location.href = '{{ route("reports.performance") }}?' + params.toString();
}
</script>
@endpush

@php
function calculatePerformanceScore($dept) {
    $score = 100;
    $overdueRate = $dept->total_handovers > 0 ? ($dept->overdue_count / $dept->total_handovers) * 100 : 0;
    if ($overdueRate > 20) $score -= 40;
    elseif ($overdueRate > 10) $score -= 20;
    return max(0, $score);
}

function calculateUserPerformanceScore($userPerf) {
    $score = 100;
    if ($userPerf->total_handovers_count >= 10) $score += 10;
    if ($userPerf->avg_duration > 24) $score -= 20;
    return max(0, min(100, $score));
}
@endphp