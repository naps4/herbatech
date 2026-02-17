@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Admin</li>
@endsection

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ \App\Models\User::count() }}</h3>
                <p>Total Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ \App\Models\CPB::count() }}</h3>
                <p>Total CPB</p>
            </div>
            <div class="icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <a href="{{ route('cpb.index') }}" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ \App\Models\CPB::where('is_overdue', true)->count() }}</h3>
                <p>CPB Overdue</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <a href="{{ route('cpb.index', ['overdue' => 'true']) }}" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ \App\Models\HandoverLog::count() }}</h3>
                <p>Total Handover</p>
            </div>
            <div class="icon">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <a href="{{ route('reports.audit') }}" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- User Statistics by Role -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">User Statistics by Role</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Jumlah User</th>
                                <th>Persentase</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalUsers = \App\Models\User::count();
                                $roles = ['superadmin', 'rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc'];
                            @endphp
                            
                            @foreach($roles as $role)
                                @php
                                    $count = \App\Models\User::where('role', $role)->count();
                                    $percentage = $totalUsers > 0 ? round(($count / $totalUsers) * 100, 2) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge badge-primary">{{ strtoupper($role) }}</span>
                                    </td>
                                    <td>{{ $count }}</td>
                                    <td>
                                        <div class="progress progress-xs">
                                            <div class="progress-bar bg-info" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="badge bg-info">{{ $percentage }}%</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.users.index', ['role' => $role]) }}" 
                                           class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th><strong>TOTAL</strong></th>
                                <th>{{ $totalUsers }}</th>
                                <th>100%</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Users -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">User Terbaru</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Tambah User
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\User::orderBy('created_at', 'desc')->take(10)->get() as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge badge-secondary">{{ strtoupper($user->role) }}</span>
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-xs btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-default btn-block">
                    Lihat Semua User
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- CPB Statistics -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">CPB Statistics</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach(['rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc', 'qa_final', 'released'] as $status)
                    <div class="col-md-3">
                        <div class="info-box">
                            <span class="info-box-icon bg-{{ $status == 'released' ? 'success' : 'info' }}">
                                <i class="fas fa-{{ $status == 'released' ? 'check' : 'sync' }}"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ strtoupper($status) }}</span>
                                <span class="info-box-number">
                                    {{ \App\Models\CPB::where('status', $status)->count() }}
                                </span>
                                <div class="progress">
                                    @php
                                        $totalCPB = \App\Models\CPB::count();
                                        $count = \App\Models\CPB::where('status', $status)->count();
                                        $percentage = $totalCPB > 0 ? round(($count / $totalCPB) * 100, 2) : 0;
                                    @endphp
                                    <div class="progress-bar" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="progress-description">
                                    {{ $percentage }}% dari total
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Quick Actions -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-12">
                        <a href="{{ route('admin.users.create') }}" class="info-box bg-gradient-success">
                            <span class="info-box-icon"><i class="fas fa-user-plus"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tambah User</span>
                                <span class="info-box-number">Baru</span>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 col-12">
                        <a href="{{ route('admin.users.index') }}" class="info-box bg-gradient-info">
                            <span class="info-box-icon"><i class="fas fa-users-cog"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kelola</span>
                                <span class="info-box-number">User</span>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 col-12">
                        <a href="{{ route('reports.index') }}" class="info-box bg-gradient-warning">
                            <span class="info-box-icon"><i class="fas fa-chart-bar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Lihat</span>
                                <span class="info-box-number">Reports</span>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 col-12">
                        <a href="{{ route('reports.export') }}" class="info-box bg-gradient-danger">
                            <span class="info-box-icon"><i class="fas fa-file-excel"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Export</span>
                                <span class="info-box-number">Excel</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load user statistics via AJAX
    $.get('/api/admin/users/stats', function(data) {
        console.log('User stats:', data);
        // Update UI if needed
    });
});
</script>
@endpush