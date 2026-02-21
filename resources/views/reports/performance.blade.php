@extends('layouts.app')

@section('title', '') 
@section('page-title', '')
@section('breadcrumb', '')

<!-- {{-- 2. Fungsi Helper (Tetap di sini, tapi pastikan variabel di dalam loop aman) --}}
@php
if (!function_exists('calculatePerformanceScore')) {
    function calculatePerformanceScore($dept) {
        $score = 100;
        $overdueRate = $dept->total_handovers > 0 ? ($dept->overdue_count / $dept->total_handovers) * 100 : 0;
        if ($overdueRate > 20) $score -= 40;
        elseif ($overdueRate > 10) $score -= 20;
        return max(0, $score);
    }
}

if (!function_exists('calculateUserPerformanceScore')) {
    function calculateUserPerformanceScore($userPerf) {
        $score = 100;
        // Gunakan Null Coalescing agar tidak error jika data kosong
        $totalHandovers = $userPerf->total_handovers_count ?? $userPerf->total_handovers ?? 0;
        if ($totalHandovers >= 10) $score += 10;
        if (($userPerf->avg_duration ?? 0) > 24) $score -= 20;
        return max(0, min(100, $score));
    }
}
@endphp -->

@section('content')
{{-- Filter Card --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter Performance</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.performance') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="start_date">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="end_date">Tanggal Akhir</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
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
                            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                            <a href="{{ route('reports.performance') }}" class="btn btn-default"><i class="fas fa-sync-alt"></i> Reset</a>
                            <button type="button" class="btn btn-success float-right" onclick="exportPerformance()"><i class="fas fa-file-excel"></i> Export Excel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Summary & Chart --}}
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
            <div class="inner"><h3>{{ $summary['total_handovers'] ?? 0 }}</h3><p>Total Handover</p></div>
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
            <div class="inner"><h3>{{ $summary['overdue_count'] ?? 0 }}</h3><p>Total Overdue</p></div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
</div>

{{-- Table Detail --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Detail Performance per Departemen</h3></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Departemen</th>
                            <th>Jumlah CPB</th>
                            <th>Avg Durasi</th>
                            <th>Overdue Rate</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($performance as $dept)
                        <tr>
                            <td><strong>{{ strtoupper($dept->from_status) }}</strong></td>
                            <td class="text-right">{{ $dept->total_handovers }}</td>
                            <td class="text-right">@if($dept->avg_duration < 1) {{ round($dept->avg_duration * 60) }} m @else {{ round($dept->avg_duration, 1) }} j @endif</td>
                            <td class="text-right">
                                @php $oRate = $dept->overdue_percentage ?? 0; @endphp
                                <span class="badge {{ $oRate > 20 ? 'bg-danger' : ($oRate > 10 ? 'bg-warning' : 'bg-success') }}">{{ round($oRate, 1) }}%</span>
                            </td>
                            <td class="text-right">
                                @php $score = calculatePerformanceScore($dept); @endphp
                                <span class="badge {{ $score >= 80 ? 'bg-success' : ($score >= 60 ? 'bg-warning' : 'bg-danger') }}">{{ $score }}%</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Top Performers --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Top Performers</h3></div>
            <div class="card-body">
                <div class="row">
                    {{-- Pastikan variabel $userPerformance ada sebelum di-loop --}}
                    @if(isset($userPerformance) && $userPerformance->count() > 0)
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
                                                <h5 class="description-header">{{ $userPerf->total_handovers_count ?? 0 }}</h5>
                                                <span class="description-text">Handover</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 border-right">
                                            <div class="description-block">
                                                <h5 class="description-header">
                                                    @if(($userPerf->avg_duration ?? 0) < 1) {{ round(($userPerf->avg_duration ?? 0) * 60) }}m @else {{ round(($userPerf->avg_duration ?? 0), 1) }}j @endif
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
                    @else
                        <div class="col-12 text-center text-muted"><p>Belum ada data performer.</p></div>
                    @endif
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
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                borderRadius: 5,
                barPercentage: 0.5, // Menghilangkan kesan "gemuk" pada batang grafik
                categoryPercentage: 0.5,
                yAxisID: 'y',
                order: 2
            },
            {
                label: '% Overdue',
                data: {!! json_encode($performance->pluck('overdue_percentage')) !!},
                type: 'line',
                borderColor: '#e74c3c',
                backgroundColor: '#e74c3c',
                borderWidth: 3,
                pointRadius: 6,
                pointBackgroundColor: '#ffffff', // Titik putih di tengah agar kontras
                pointBorderWidth: 3,
                tension: 0.4, // Membuat garis melengkung halus (smooth line)
                yAxisID: 'y1',
                order: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' }, // Posisi legenda di atas
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                padding: 12,
                cornerRadius: 8
            }
        },
        scales: {
            y: { 
                beginAtZero: true,
                title: { display: true, text: 'Waktu Pengerjaan' },
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: { callback: (v) => formatTimeLabel(v) } 
            },
            y1: { 
                position: 'right', 
                min: 0, 
                max: 100,
                title: { display: true, text: 'Tingkat Keterlambatan (%)' },
                grid: { display: false }, // Matikan grid kanan agar tidak tumpang tindih
                ticks: { callback: (v) => v + '%' } 
            }
        }
    }
});

    // logika auto log

    console.log("Auto-refresh aktif...");
    
    setInterval(function() {
        // Hanya refresh jika user sedang di paling atas (tidak sedang baca tabel di bawah)
        if ($(window).scrollTop() === 0) {
            console.log("Merefresh halaman untuk update data CPB...");
            location.reload();
        }
    }, 30000); // 30 detik

});

function exportPerformance() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'true');
    window.location.href = '{{ route("reports.performance") }}?' + params.toString();
}
</script>
@endpush