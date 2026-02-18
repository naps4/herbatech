{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="row">
    <!-- Stats Cards -->
    @include('dashboard.partials.stats')
    
    <!-- Quick Actions Card -->
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    @can('create', App\Models\CPB::class)
                    <div class="col-lg-3 col-6">
                        <a href="{{ route('cpb.create') }}" class="btn btn-app bg-success">
                            <i class="fas fa-plus"></i> Buat CPB
                        </a>
                    </div>
                    @endcan
                    
                    <div class="col-lg-3 col-6">
 
                        <a href="{{ route('cpb.index', ['overdue' => 'true', 'status' => 'all']) }}" class="btn btn-app bg-danger">
                            <i class="fas fa-exclamation-triangle"></i> Overdue
                            @php
                                // Perbaikan Badge: Agar angka badge sinkron dengan apa yang akan dilihat user
                                $user = auth()->user();
                                $overdueQuery = \App\Models\CPB::where('is_overdue', true);
                                
                                if (!$user->isSuperAdmin() && !$user->isQA()) {
                                    $overdueQuery->where(function($q) use ($user) {
                                        $q->where('status', $user->role)
                                        ->orWhere('created_by', $user->id);
                                    });
                                }
                                $overdueCount = $overdueQuery->count();
                            @endphp
                            @if($overdueCount > 0)
                                <span class="badge bg-warning">{{ $overdueCount }}</span>
                            @endif
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <a href="{{ route('notifications.index') }}" class="btn btn-app bg-info">
                            <i class="fas fa-bell"></i> Notifikasi
                            @php
                                $unreadCount = auth()->user()->unreadNotifications()->count();
                            @endphp
                            @if($unreadCount > 0)
                                <span class="badge bg-danger">{{ $unreadCount }}</span>
                            @endif
                        </a>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <a href="{{ route('reports.index') }}" class="btn btn-app bg-warning">
                            <i class="fas fa-chart-bar"></i> Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- CPB List -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">CPB Aktif</h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 200px;">
                        <input type="text" id="search-cpb" class="form-control float-right" placeholder="Cari CPB...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-default" id="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover" id="cpb-table">
                        <thead>
                            <tr>
                                <th>
                                    <a href="javascript:void(0)" class="sort-header" data-sort="batch_number">
                                        No. Batch <i class="fas fa-sort"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="javascript:void(0)" class="sort-header" data-sort="type">
                                        Jenis <i class="fas fa-sort"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="javascript:void(0)" class="sort-header" data-sort="product_name">
                                        Produk <i class="fas fa-sort"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="javascript:void(0)" class="sort-header" data-sort="status">
                                        Lokasi <i class="fas fa-sort"></i>
                                    </a>
                                </th>
                                <th>
                                    <a href="javascript:void(0)" class="sort-header" data-sort="duration">
                                        Durasi <i class="fas fa-sort"></i>
                                    </a>
                                </th>
                                <th>Status Waktu</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cpbs as $cpb)
                                <tr class="{{ $cpb->is_overdue ? 'table-danger' : '' }} cpb-row" 
                                    data-batch="{{ strtolower($cpb->batch_number) }}"
                                    data-product="{{ strtolower($cpb->product_name) }}"
                                    data-status="{{ $cpb->status }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <strong>{{ $cpb->batch_number }}</strong>
                                            @if($cpb->is_overdue)
                                                <span class="badge bg-danger ml-2">OVERDUE</span>
                                            @endif
                                            @if($cpb->status === 'released')
                                                <span class="badge bg-success ml-2">RELEASED</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">
                                            Dibuat: {{ $cpb->created_at->format('d/m/Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $cpb->type == 'pengolahan' ? 'bg-info' : 'bg-primary' }}">
                                            {{ ucfirst($cpb->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $cpb->product_name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            Durasi: {{ $cpb->schedule_duration }} jam
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            {!! $cpb->status_badge !!}
                                            @if($cpb->currentDepartment)
                                                <small class="text-muted mt-1">
                                                    {{ $cpb->currentDepartment->name }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="progress-group">
                                            <div class="progress progress-xs">
                                                @php
                                                    $percentage = ($cpb->time_limit > 0) ? min(100, ($cpb->duration_in_current_status / $cpb->time_limit) * 100) : 0;
                                                    $color = $cpb->is_overdue ? 'danger' : ($percentage > 80 ? 'warning' : 'success');
                                                @endphp
                                                <div class="progress-bar bg-{{ $color }}" style="width: {{ $percentage }}%"></div>
                                            </div>
                                            <span class="badge bg-secondary">
                                                {{ $cpb->duration_in_current_status }}/{{ $cpb->time_limit }} jam
                                            </span>
                                        </div>
                                    </td>
                                    <td>{!! $cpb->time_status_badge !!}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('cpb.show', $cpb) }}" class="btn btn-sm btn-info" 
                                               data-toggle="tooltip" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($cpb->canBeHandedOverBy(auth()->user()))
                                                <button class="btn btn-sm btn-success handover-btn" 
                                                        data-toggle="modal" 
                                                        data-target="#handoverModal{{ $cpb->id }}"
                                                        data-toggle="tooltip" title="Serahkan">
                                                    <i class="fas fa-forward"></i>
                                                </button>
                                            @endif
                                            @if($cpb->status == 'qa_final' && (auth()->user()->isQA() || auth()->user()->isSuperAdmin()))
                                                <form action="{{ route('cpb.release', $cpb) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            data-toggle="tooltip" title="Release CPB"
                                                            onclick="return confirm('Release CPB {{ $cpb->batch_number }}?')">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Handover Modal -->
                                <div class="modal fade" id="handoverModal{{ $cpb->id }}">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('cpb.handover', $cpb) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Serahkan CPB</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-info">
                                                        <h6><i class="icon fas fa-info-circle"></i> Informasi CPB</h6>
                                                        <p class="mb-1"><strong>{{ $cpb->batch_number }}</strong> - {{ $cpb->product_name }}</p>
                                                        <p class="mb-1">Status saat ini: {!! $cpb->status_badge !!}</p>
                                                        <p class="mb-0">Durasi: {{ $cpb->duration_in_current_status }} jam</p>
                                                    </div>
                                                    
                                                    <p>Anda akan menyerahkan CPB <strong>{{ $cpb->batch_number }}</strong> ke departemen berikutnya: 
                                                        <span class="badge bg-success">{{ strtoupper($cpb->getNextDepartment()) }}</span>
                                                    </p>
                                                    
                                                    <div class="form-group">
                                                        <label>Catatan (opsional):</label>
                                                        <textarea name="notes" class="form-control" rows="3" 
                                                                  placeholder="Tambahkan catatan mengenai handover ini..."></textarea>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" id="confirmHandover{{ $cpb->id }}" required>
                                                            <label class="custom-control-label" for="confirmHandover{{ $cpb->id }}">
                                                                Saya menyatakan bahwa CPB ini siap untuk diserahkan
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer justify-content-between">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Konfirmasi Serah Terima</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                                            <h4 class="text-muted">Tidak ada CPB aktif</h4>
                                            <p class="text-muted">Belum ada CPB yang sedang diproses</p>
                                            @can('create', App\Models\CPB::class)
                                            <a href="{{ route('cpb.create') }}" class="btn btn-primary mt-2">
                                                <i class="fas fa-plus"></i> Buat CPB Pertama
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">
                            Menampilkan {{ $cpbs->count() }} dari {{ $cpbs->total() }} CPB
                        </small>
                    </div>
                    <div>
                        {{ $cpbs->links() }}
                    </div>
                    <div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{ route('reports.export') }}?type=active">
                                    <i class="fas fa-file-excel text-success"></i> Excel
                                </a>
                                <a class="dropdown-item" href="{{ route('cpb.export-pdf') }}">
                                    <i class="fas fa-file-pdf text-danger"></i> PDF
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-print"></i> Print
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Handovers -->
    @if(auth()->user()->handoversGiven->count() > 0 || auth()->user()->handoversReceived->count() > 0)
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Handover Terbaru</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @php
                        $recentHandovers = \App\Models\HandoverLog::where('handed_by', auth()->id())
                            ->orWhere('received_by', auth()->id())
                            ->orderBy('handed_at', 'desc')
                            ->take(5)
                            ->get();
                    @endphp
                    
                    @foreach($recentHandovers as $handover)
                    <div class="time-label">
                        <span class="bg-{{ $handover->was_overdue ? 'danger' : 'info' }}">
                            {{ $handover->handed_at->format('d M') }}
                        </span>
                    </div>
                    
                    <div>
                        <i class="fas fa-exchange-alt bg-blue"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="fas fa-clock"></i> {{ $handover->handed_at->format('H:i') }}
                            </span>
                            <h3 class="timeline-header">
                                <strong>{{ $handover->sender->name }}</strong> 
                                → 
                                <strong>{{ $handover->receiver->name ?? 'Belum diterima' }}</strong>
                            </h3>
                            <div class="timeline-body">
                                <strong>{{ $handover->cpb->batch_number }}</strong> - 
                                {{ $handover->from_status }} → {{ $handover->to_status }}
                                @if($handover->was_overdue)
                                    <span class="badge badge-danger ml-2">Overdue</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Department Statistics -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Statistik Departemen</h3>
            </div>
            <div class="card-body">
                <canvas id="departmentChart" height="150"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.progress-group {
    min-width: 150px;
}
.timeline {
    position: relative;
    padding: 0;
    list-style: none;
}
.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #dee2e6;
    left: 31px;
    margin: 0;
    border-radius: 2px;
}
.timeline > div {
    position: relative;
    margin-bottom: 20px;
}
.time-label span {
    padding: 5px 10px;
    color: white;
    border-radius: 4px;
    display: inline-block;
}
.timeline-item {
    position: relative;
    margin-left: 60px;
    margin-right: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}
.timeline-header {
    margin: 0 0 10px 0;
    font-size: 14px;
}
.timeline-body {
    padding: 10px;
}
.timeline-time {
    color: #999;
    font-size: 12px;
}
.timeline > div > .fa {
    position: absolute;
    left: 23px;
    top: 0;
    width: 16px;
    height: 16px;
    color: #fff;
    background: #6c757d;
    border-radius: 50%;
    text-align: center;
    line-height: 16px;
    font-size: 10px;
}
.empty-state {
    text-align: center;
    padding: 40px 0;
}
.btn-app {
    position: relative;
    padding: 15px 5px;
    margin: 0 0 10px 10px;
    min-width: 80px;
    height: 60px;
    text-align: center;
    border-radius: 0;
}
.btn-app > .fa, .btn-app > .fas, .btn-app > .far, .btn-app > .fab, .btn-app > .glyphicon, .btn-app > .ion {
    font-size: 20px;
    display: block;
}
.sort-header {
    color: #495057;
    text-decoration: none;
}
.sort-header:hover {
    color: #007bff;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Auto-refresh every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
    
    // Enable tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Search functionality
    $('#search-cpb').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('.cpb-row').each(function() {
            const batch = $(this).data('batch');
            const product = $(this).data('product');
            const status = $(this).data('status');
            
            if (batch.includes(searchTerm) || 
                product.includes(searchTerm) || 
                status.includes(searchTerm) ||
                searchTerm === '') {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    $('#search-btn').click(function() {
        $('#search-cpb').trigger('keyup');
    });
    
    // Sort functionality
    let sortDirection = {};
    
    $('.sort-header').click(function() {
        const sortBy = $(this).data('sort');
        const $rows = $('.cpb-row').get();
        
        // Toggle sort direction
        sortDirection[sortBy] = !sortDirection[sortBy];
        const direction = sortDirection[sortBy] ? 1 : -1;
        
        // Update sort icon
        $(this).find('i').removeClass('fa-sort fa-sort-up fa-sort-down');
        $(this).find('i').addClass(direction === 1 ? 'fa-sort-up' : 'fa-sort-down');
        
        // Reset other sort icons
        $('.sort-header').not(this).find('i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        
        // Sort rows
        $rows.sort(function(a, b) {
            const $a = $(a);
            const $b = $(b);
            
            let valA, valB;
            
            switch(sortBy) {
                case 'batch_number':
                    valA = $a.find('td:eq(0) strong').text();
                    valB = $b.find('td:eq(0) strong').text();
                    break;
                case 'type':
                    valA = $a.find('td:eq(1) .badge').text();
                    valB = $b.find('td:eq(1) .badge').text();
                    break;
                case 'product_name':
                    valA = $a.find('td:eq(2) strong').text();
                    valB = $b.find('td:eq(2) strong').text();
                    break;
                case 'status':
                    valA = $a.data('status');
                    valB = $b.data('status');
                    break;
                case 'duration':
                    valA = parseInt($a.find('td:eq(4) .badge').text().split('/')[0]);
                    valB = parseInt($b.find('td:eq(4) .badge').text().split('/')[0]);
                    break;
                default:
                    return 0;
            }
            
            if (valA < valB) return -1 * direction;
            if (valA > valB) return 1 * direction;
            return 0;
        });
        
        // Reorder table
        $.each($rows, function(index, row) {
            $('#cpb-table tbody').append(row);
        });
    });
    
    // Department Statistics Chart
    @php
        $departmentStats = [
            'rnd' => \App\Models\CPB::where('status', 'rnd')->count(),
            'qa' => \App\Models\CPB::where('status', 'qa')->count(),
            'ppic' => \App\Models\CPB::where('status', 'ppic')->count(),
            'wh' => \App\Models\CPB::where('status', 'wh')->count(),
            'produksi' => \App\Models\CPB::where('status', 'produksi')->count(),
            'qc' => \App\Models\CPB::where('status', 'qc')->count(),
            'qa_final' => \App\Models\CPB::where('status', 'qa_final')->count(),
            'released' => \App\Models\CPB::where('status', 'released')->count(),
        ];
    @endphp
    
    const ctx = document.getElementById('departmentChart').getContext('2d');
    const departmentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['RND', 'QA', 'PPIC', 'WH', 'Produksi', 'QC', 'QA Final', 'Released'],
            datasets: [{
                label: 'Jumlah CPB',
                data: [
                    {{ $departmentStats['rnd'] }},
                    {{ $departmentStats['qa'] }},
                    {{ $departmentStats['ppic'] }},
                    {{ $departmentStats['wh'] }},
                    {{ $departmentStats['produksi'] }},
                    {{ $departmentStats['qc'] }},
                    {{ $departmentStats['qa_final'] }},
                    {{ $departmentStats['released'] }}
                ],
                backgroundColor: [
                    '#007bff', '#17a2b8', '#6c757d', '#343a40',
                    '#ffc107', '#17a2b8', '#28a745', '#20c997'
                ],
                borderColor: [
                    '#0056b3', '#117a8b', '#545b62', '#23272b',
                    '#d39e00', '#117a8b', '#1e7e34', '#17a589'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Auto-open handover modal if URL has parameter
    const urlParams = new URLSearchParams(window.location.search);
    const handoverCpb = urlParams.get('handover');
    if (handoverCpb) {
        $('#handoverModal' + handoverCpb).modal('show');
    }
    
    // Refresh stats every minute
    setInterval(function() {
        $.get('/api/dashboard/stats', function(data) {
            // Update stats cards if needed
            console.log('Stats updated:', data);
        });
    }, 60000);
});
</script>
@endpush