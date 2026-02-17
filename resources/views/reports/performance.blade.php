@extends('layouts.app')

@section('title', 'Performance Report')
@section('page-title', 'Laporan Performance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Laporan</a></li>
    <li class="breadcrumb-item active">Performance</li>
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
                                    <option value="rnd" {{ request('department') == 'rnd' ? 'selected' : '' }}>RND</option>
                                    <option value="qa" {{ request('department') == 'qa' ? 'selected' : '' }}>QA</option>
                                    <option value="ppic" {{ request('department') == 'ppic' ? 'selected' : '' }}>PPIC</option>
                                    <option value="wh" {{ request('department') == 'wh' ? 'selected' : '' }}>Warehouse</option>
                                    <option value="produksi" {{ request('department') == 'produksi' ? 'selected' : '' }}>Produksi</option>
                                    <option value="qc" {{ request('department') == 'qc' ? 'selected' : '' }}>QC</option>
                                    <option value="qa_final" {{ request('department') == 'qa_final' ? 'selected' : '' }}>QA Final</option>
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
    <!-- Performance Summary -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Performance per Departemen</h3>
            </div>
            <div class="card-body">
                <canvas id="performanceChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ringkasan Performance</h3>
            </div>
            <div class="card-body">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $summary['total_handovers'] ?? 0 }}</h3>
                        <p>Total Handover</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
                
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ round($summary['avg_duration'] ?? 0, 1) }}<sup style="font-size: 20px">jam</sup></h3>
                        <p>Rata-rata Durasi</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $summary['overdue_count'] ?? 0 }}</h3>
                        <p>Total Overdue</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail Performance per Departemen</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Departemen</th>
                                <th>Jumlah CPB</th>
                                <th>Rata-rata Durasi (jam)</th>
                                <th>Durasi Tercepat</th>
                                <th>Durasi Terlama</th>
                                <th>Jumlah Overdue</th>
                                <th>% Overdue</th>
                                <th>Performance Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($performance as $dept)
                                <tr>
                                    <td>
                                        <strong>{{ ucfirst($dept->from_status) }}</strong>
                                    </td>
                                    <td class="text-right">{{ $dept->total_handovers }}</td>
                                    <td class="text-right">{{ round($dept->avg_duration, 1) }}</td>
                                    <td class="text-right">{{ $dept->min_duration ?? '-' }}</td>
                                    <td class="text-right">{{ $dept->max_duration ?? '-' }}</td>
                                    <td class="text-right">{{ $dept->overdue_count }}</td>
                                    <td class="text-right">
                                        @php
                                            $overduePercentage = $dept->total_handovers > 0 
                                                ? round(($dept->overdue_count / $dept->total_handovers) * 100, 1) 
                                                : 0;
                                        @endphp
                                        <span class="badge {{ $overduePercentage > 20 ? 'bg-danger' : ($overduePercentage > 10 ? 'bg-warning' : 'bg-success') }}">
                                            {{ $overduePercentage }}%
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        @php
                                            $score = calculatePerformanceScore($dept);
                                        @endphp
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
            <div class="card-header">
                <h3 class="card-title">Top Performers</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($userPerformance->take(5) as $userPerf)
                    <div class="col-md-4">
                        <div class="card card-widget widget-user">
                            <div class="widget-user-header bg-info">
                                <h3 class="widget-user-username">{{ $userPerf->sender->name }}</h3>
                                <h5 class="widget-user-desc">{{ $userPerf->sender->department }}</h5>
                            </div>
                            <div class="widget-user-image">
                                <img class="img-circle elevation-2" src="{{ asset('vendor/adminlte/dist/img/user1-128x128.jpg') }}" alt="User Avatar">
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-sm-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header">{{ $userPerf->total_handovers }}</h5>
                                            <span class="description-text">Handover</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 border-right">
                                        <div class="description-block">
                                            <h5 class="description-header">{{ round($userPerf->avg_duration, 1) }}</h5>
                                            <span class="description-text">Avg Hours</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="description-block">
                                            @php
                                                $userScore = calculateUserPerformanceScore($userPerf);
                                            @endphp
                                            <h5 class="description-header">{{ $userScore }}%</h5>
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

@push('styles')
<style>
.widget-user .widget-user-header {
    padding: 20px;
    height: 120px;
    border-top-left-radius: .25rem;
    border-top-right-radius: .25rem;
}
.widget-user .widget-user-image {
    position: absolute;
    top: 65px;
    left: 50%;
    margin-left: -45px;
}
.widget-user .card-footer {
    padding-top: 50px;
}
.description-block {
    text-align: center;
}
.description-header {
    font-size: 1.2rem;
    font-weight: bold;
}
.description-text {
    font-size: 0.9rem;
    color: #6c757d;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Performance Chart
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const performanceChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($performance->pluck('from_status')->map(function($item) { return ucfirst($item); })) !!},
            datasets: [
                {
                    label: 'Rata-rata Durasi (jam)',
                    data: {!! json_encode($performance->pluck('avg_duration')) !!},
                    backgroundColor: '#007bff',
                    borderColor: '#0056b3',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: '% Overdue',
                    data: {!! json_encode($performance->map(function($item) { 
                        return $item->total_handovers > 0 ? round(($item->overdue_count / $item->total_handovers) * 100, 1) : 0; 
                    })) !!},
                    backgroundColor: '#dc3545',
                    borderColor: '#a71d2a',
                    borderWidth: 1,
                    type: 'line',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Durasi (jam)'
                    },
                    ticks: {
                        beginAtZero: true
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: '% Overdue'
                    },
                    ticks: {
                        beginAtZero: true
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.datasetIndex === 0) {
                                label += context.parsed.y + ' jam';
                            } else {
                                label += context.parsed.y + '%';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
    
    // Export function
    function exportPerformance() {
        const params = new URLSearchParams(window.location.search);
        params.set('export', 'true');
        window.location.href = '{{ route("reports.performance") }}?' + params.toString();
    }
    
    // Auto-refresh chart data every 5 minutes
    setInterval(function() {
        $.get('{{ route("reports.performance") }}?ajax=true', function(data) {
            if (data.performance) {
                performanceChart.data.datasets[0].data = data.performance.map(p => p.avg_duration);
                performanceChart.data.datasets[1].data = data.performance.map(p => 
                    p.total_handovers > 0 ? round((p.overdue_count / p.total_handovers) * 100, 1) : 0
                );
                performanceChart.update();
            }
        });
    }, 300000);
});
</script>
@endpush

<?php
// Helper functions untuk view
function calculatePerformanceScore($dept) {
    $score = 100;
    
    // Penalty untuk overdue rate
    $overdueRate = $dept->total_handovers > 0 ? ($dept->overdue_count / $dept->total_handovers) * 100 : 0;
    if ($overdueRate > 20) {
        $score -= 30;
    } elseif ($overdueRate > 10) {
        $score -= 15;
    } elseif ($overdueRate > 5) {
        $score -= 5;
    }
    
    // Bonus untuk avg duration rendah
    $targetDuration = getTargetDuration($dept->from_status);
    if ($dept->avg_duration < $targetDuration * 0.8) {
        $score += 10;
    } elseif ($dept->avg_duration < $targetDuration) {
        $score += 5;
    }
    
    return max(0, min(100, round($score)));
}

function calculateUserPerformanceScore($userPerf) {
    $score = 100;
    
    // Base score dari jumlah handover
    if ($userPerf->total_handovers >= 10) {
        $score += 10;
    } elseif ($userPerf->total_handovers >= 5) {
        $score += 5;
    }
    
    // Penalty untuk avg duration tinggi
    if ($userPerf->avg_duration > 48) {
        $score -= 20;
    } elseif ($userPerf->avg_duration > 24) {
        $score -= 10;
    }
    
    return max(0, min(100, round($score)));
}

function getTargetDuration($department) {
    $targets = [
        'rnd' => 24,
        'qa' => 24,
        'ppic' => 4,
        'wh' => 24,
        'produksi' => 48,
        'qc' => 4,
        'qa_final' => 24
    ];
    
    return $targets[$department] ?? 24;
}
?>