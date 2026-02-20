{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', '')


@section('content')
<div class="row">
    @include('dashboard.partials.stats')
    
    <div class="col-12">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                Quick Actions
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @php $user = auth()->user(); @endphp

                    {{-- 1. Buat CPB --}}
                    @can('create', App\Models\CPB::class)
                    <div class="col-lg-2-4 col-md-4 col-6 mb-3">
                        <a href="{{ route('cpb.create') }}" class="btn btn-app bg-success d-block w-100 m-0 py-3 shadow-sm h-100 border-0">
                            <i class="fas fa-plus"></i> Buat CPB
                        </a>
                    </div>
                    @endcan

                    {{-- 2. Rework - Filter Rework=true --}}
                    <div class="col-lg-2-4 col-md-4 col-6 mb-3">
                        <a href="{{ route('cpb.index', ['rework' => 'true', 'status' => 'all']) }}" 
                           class="btn btn-app bg-warning d-block w-100 m-0 py-3 shadow-sm h-100 border-0">
                            @php
                                $reworkQuery = \App\Models\CPB::where('is_rework', true);
                                if (!$user->isSuperAdmin() && !$user->isQA() && !$user->isRND()) {
                                    $reworkQuery->where(function($q) use ($user) {
                                        $q->where('status', $user->role)->orWhere('created_by', $user->id);
                                    });
                                }
                                $reworkCount = $reworkQuery->count();
                            @endphp
                            @if($reworkCount > 0)
                                <span class="badge badge-light border text-dark">{{ $reworkCount }}</span>
                            @endif
                            <i class="fas fa-undo text-dark"></i> <span class="text-dark font-weight-bold">Rework</span>
                        </a>
                    </div>

                    {{-- 3. Overdue --}}
                    <div class="col-lg-2-4 col-md-4 col-6 mb-3">
                        <a href="{{ route('cpb.index', ['overdue' => 'true', 'status' => 'all']) }}" 
                           class="btn btn-app bg-danger d-block w-100 m-0 py-3 shadow-sm h-100 border-0">
                            @php
                                $overdueQuery = \App\Models\CPB::where('is_overdue', true);
                                if (!$user->isSuperAdmin() && !$user->isQA() && !$user->isRND()) {
                                    $overdueQuery->where(function($q) use ($user) {
                                        $q->where('status', $user->role)->orWhere('created_by', $user->id);
                                    });
                                }
                                $overdueCount = $overdueQuery->count();
                            @endphp
                            @if($overdueCount > 0)
                                <span class="badge badge-light border text-danger">{{ $overdueCount }}</span>
                            @endif
                            <i class="fas fa-exclamation-triangle"></i> <span class="font-weight-bold">Overdue</span>
                        </a>
                    </div>

                    {{-- 4. Notifikasi --}}
                    <div class="col-lg-2-4 col-md-4 col-6 mb-3">
                        <a href="{{ route('notifications.index') }}" class="btn btn-app bg-info d-block w-100 m-0 py-3 shadow-sm h-100 border-0">
                            @php $unreadCount = $user->unreadNotifications()->count(); @endphp
                            @if($unreadCount > 0)
                                <span class="badge badge-danger">{{ $unreadCount }}</span>
                            @endif
                            <i class="fas fa-bell"></i> Notifikasi
                        </a>
                    </div>

                    {{-- 5. Laporan --}}
                    <div class="col-lg-2-4 col-md-4 col-6 mb-3">
                        <a href="{{ route('reports.index') }}" class="btn btn-app bg-secondary d-block w-100 m-0 py-3 shadow-sm h-100 border-0">
                            <i class="fas fa-chart-bar"></i> Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">CPB Aktif</h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" id="search-cpb" class="form-control float-right" placeholder="Cari No. Batch...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-default" id="search-btn"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover" id="cpb-table">
                        <thead>
                            <tr>
                                <th>No. Batch</th>
                                <th>Jenis</th>
                                <th>Produk</th>
                                <th>Lokasi</th>
                                <th>Durasi</th>
                                <th>Status Waktu</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cpbs as $cpb)
                                <tr class="{{ $cpb->is_overdue ? 'table-danger' : ($cpb->is_rework ? 'table-warning' : '') }} cpb-row" 
                                    data-batch="{{ strtolower($cpb->batch_number) }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <strong>{{ $cpb->batch_number }}</strong>
                                            @if($cpb->is_rework)
                                                <span class="badge bg-orange ml-2" title="Alasan: {{ $cpb->rework_note }}"><i class="fas fa-undo"></i> REWORK</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td><span class="badge {{ $cpb->type == 'pengolahan' ? 'bg-info' : 'bg-primary' }}">{{ ucfirst($cpb->type) }}</span></td>
                                    <td>{{ $cpb->product_name }}</td>
                                    <td>{!! $cpb->status_badge !!}</td>
                                    <td>
                                        @php
                                            $percentage = ($cpb->time_limit > 0) ? min(100, ($cpb->duration_in_current_status / $cpb->time_limit) * 100) : 0;
                                            $color = $cpb->is_overdue ? 'danger' : ($percentage > 80 ? 'warning' : 'success');
                                        @endphp
                                        <div class="progress progress-xs">
                                            <div class="progress-bar bg-{{ $color }}" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <small>{{ $cpb->duration_in_current_status }}/{{ $cpb->time_limit }} jam</small>
                                    </td>
                                    <td>{!! $cpb->time_status_badge !!}</td>
                                    <td>
                                        <a href="{{ route('cpb.show', $cpb) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center py-5">Tidak ada CPB aktif</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div><small class="text-muted">Menampilkan {{ $cpbs->count() }} CPB</small></div>
                    <div>{{ $cpbs->appends(request()->query())->links() }}</div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="{{ route('reports.export', ['type' => 'active']) }}">
                                <i class="fas fa-file-excel text-success"></i> Excel
                            </a>
                            <a class="dropdown-item" href="{{ route('cpb.export-pdf') }}">
                                <i class="fas fa-file-pdf text-danger"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Handover Terbaru</h3></div>
            <div class="card-body">
                <div class="timeline">
                    @php
                        $recentHandovers = \App\Models\HandoverLog::where('handed_by', auth()->id())->orWhere('received_by', auth()->id())
                            ->orderBy('handed_at', 'desc')->take(5)->get();
                    @endphp
                    @foreach($recentHandovers as $handover)
                        <div class="time-label"><span class="bg-info">{{ $handover->handed_at->format('d M') }}</span></div>
                        <div>
                            <i class="fas fa-exchange-alt bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $handover->handed_at->format('H:i') }}</span>
                                <h3 class="timeline-header"><strong>{{ $handover->sender->name }}</strong> → <strong>{{ $handover->receiver->name ?? 'System' }}</strong></h3>
                                <div class="timeline-body">{{ $handover->cpb->batch_number }} - {{ $handover->from_status }} → {{ $handover->to_status }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Statistik Departemen</h3></div>
            <div class="card-body">
                <canvas id="departmentChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* CSS Match Parent & 5 Columns Desktop */
    .col-lg-2-4 { position: relative; width: 100%; padding-right: 7.5px; padding-left: 7.5px; flex: 0 0 20%; max-width: 20%; }
    @media (max-width: 992px) { .col-lg-2-4 { flex: 0 0 33.33%; max-width: 33.33%; } }
    @media (max-width: 576px) { .col-lg-2-4 { flex: 0 0 50%; max-width: 50%; } }

    .btn-app {
        position: relative;
        padding: 20px 5px !important;
        margin: 0 !important;
        min-width: 0 !important;
        height: 100% !important;
        text-align: center;
        border-radius: 8px !important;
        display: flex !important;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-app:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important; }
    .btn-app > .fas { font-size: 26px !important; margin-bottom: 10px; display: block; }
    .btn-app > .badge { position: absolute; top: -5px; right: -5px; font-size: 12px; padding: 4px 8px; border-radius: 50px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
    .bg-warning.btn-app { color: #1f2d3d !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Live Search Table
    $('#search-cpb').on('keyup', function() {
        const term = $(this).val().toLowerCase();
        $('.cpb-row').each(function() {
            $(this).toggle($(this).data('batch').includes(term));
        });
    });

    // Department Chart
    @php
        $stats = ['rnd','qa','ppic','wh','produksi','qc','qa_final','released'];
        $chartData = [];
        foreach($stats as $s) { $chartData[] = \App\Models\CPB::where('status', $s)->count(); }
    @endphp
    const ctx = document.getElementById('departmentChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['RND', 'QA', 'PPIC', 'WH', 'Produksi', 'QC', 'QA Final', 'Released'],
            datasets: [{
                label: 'Jumlah CPB',
                data: @json($chartData),
                backgroundColor: ['#007bff', '#17a2b8', '#6c757d', '#343a40', '#ffc107', '#17a2b8', '#28a745', '#20c997'],
                borderWidth: 1
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }, plugins: { legend: { display: false } } }
    });
});
</script>
@endpush