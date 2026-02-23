@extends('layouts.admin')

@section('admin-title', 'Detail User: ' . $user->name)
@section('page-title', '') {{-- Dikosongkan agar tidak duplikat dengan header dashboard --}}

@section('admin-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">User Management</a></li>
    <li class="breadcrumb-item active">Profil User</li>
@endsection

@section('admin-content')
<div class="container-fluid px-md-4">
    <div class="row">
        <div class="col-xl-4 col-lg-5">
            <div class="card card-primary card-outline shadow-sm border-0">
                <div class="card-body box-profile">
                    <div class="text-center">
                        {{-- Avatar Inisial Besar --}}
                        <div class="avatar-profile mx-auto mb-3 shadow-sm">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <h3 class="profile-username text-center font-weight-bold text-dark mb-1">{{ $user->name }}</h3>
                        <div class="mb-3">{!! $user->role_badge !!}</div>
                    </div>

                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item border-top-0 px-0">
                            <b class="text-muted"><i class="fas fa-envelope mr-2"></i> Email</b> 
                            <span class="float-right text-dark">{{ $user->email }}</span>
                        </li>
                        <li class="list-group-item px-0">
                            <b class="text-muted"><i class="fas fa-building mr-2"></i> Departemen</b> 
                            <span class="float-right text-dark">{{ $user->department ?? '-' }}</span>
                        </li>
                        <li class="list-group-item px-0">
                            <b class="text-muted"><i class="fas fa-calendar-alt mr-2"></i> Bergabung</b> 
                            <span class="float-right text-dark">{{ $user->created_at->format('d M Y') }}</span>
                        </li>
                        <li class="list-group-item border-bottom-0 px-0">
                            <b class="text-muted"><i class="fas fa-history mr-2"></i> Login Terakhir</b> 
                            <span class="float-right text-dark small text-right">
                                {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Belum pernah' }}
                            </span>
                        </li>
                    </ul>

                    <div class="row pt-2">
                    <div class="mt-9">
                            <a href="{{ route('profile.edit') }}" class="btn btn-warning">
                                <i class="fas fa-user-edit"></i> Edit Profil Saya
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="info-box shadow-none border">
                        <span class="info-box-icon bg-primary-soft text-primary"><i class="fas fa-file-invoice"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text small">CPB Dibuat</span>
                            <span class="info-box-number h5 mb-0">{{ $user->cpbsCreated->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-box shadow-none border">
                        <span class="info-box-icon bg-success-soft text-success"><i class="fas fa-paper-plane"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text small">Handover Out</span>
                            <span class="info-box-number h5 mb-0">{{ $user->handoversGiven->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-box shadow-none border">
                        <span class="info-box-icon bg-warning-soft text-warning"><i class="fas fa-hand-holding"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text small">Handover In</span>
                            <span class="info-box-number h5 mb-0">{{ $user->handoversReceived->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="info-box shadow-none border text-danger">
                        <span class="info-box-icon bg-danger-soft text-danger"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text small font-weight-bold">Current CPB</span>
                            <span class="info-box-number h5 mb-0 font-weight-bold">{{ $user->cpbsCurrent->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header p-2 bg-white border-bottom">
                    <ul class="nav nav-pills small font-weight-bold" id="userTabs">
                        <li class="nav-item"><a class="nav-link active" href="#cpb_list" data-toggle="tab">RIWAYAT CPB</a></li>
                        <li class="nav-item"><a class="nav-link" href="#handover_list" data-toggle="tab">RIWAYAT HANDOVER</a></li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content">
                        <div class="tab-pane active fade show" id="cpb_list">
                            <div class="table-responsive">
                                <table class="table table-hover table-valign-middle mb-0 border-0">
                                    <thead class="bg-light">
                                        <tr class="small text-muted text-uppercase">
                                            <th class="pl-4">No. Batch</th>
                                            <th>Jenis</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-right pr-4">Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($user->cpbsCreated->take(10) as $cpb)
                                        <tr>
                                            <td class="pl-4 font-weight-bold">
                                                <a href="{{ route('cpb.show', $cpb) }}" class="text-primary">{{ $cpb->batch_number }}</a>
                                            </td>
                                            <td class="small">{{ ucfirst($cpb->type) }}</td>
                                            <td class="text-center">{!! $cpb->status_badge !!}</td>
                                            <td class="text-right pr-4 text-muted small">{{ $cpb->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center py-4 text-muted small font-italic">Belum ada CPB yang dibuat.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="tab-pane fade" id="handover_list">
                            <div class="table-responsive">
                                <table class="table table-hover table-valign-middle mb-0 border-0">
                                    <thead class="bg-light">
                                        <tr class="small text-muted text-uppercase">
                                            <th class="pl-4">CPB</th>
                                            <th>Alur Status</th>
                                            <th class="text-right pr-4">Waktu Kejadian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($user->handoversGiven->take(10) as $handover)
                                        <tr>
                                            <td class="pl-4 font-weight-bold">
                                                <a href="{{ route('cpb.show', $handover->cpb) }}" class="text-dark">{{ $handover->cpb->batch_number }}</a>
                                            </td>
                                            <td class="small">
                                                <span class="badge badge-light border text-muted">{{ strtoupper($handover->from_status) }}</span>
                                                <i class="fas fa-long-arrow-alt-right mx-1 text-muted"></i>
                                                <span class="badge badge-light border text-muted">{{ strtoupper($handover->to_status) }}</span>
                                            </td>
                                            <td class="text-right pr-4 text-muted small">{{ $handover->handed_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center py-4 text-muted small font-italic">Belum ada riwayat serah terima.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Profile Styles */
    .avatar-profile {
        width: 100px; height: 100px; border-radius: 12px;
        background: #f1f5f9; border: 2px solid #e2e8f0;
        display: flex; align-items: center; justify-content: center;
        font-size: 40px; font-weight: 800; color: #64748b;
    }
    .list-group-item { font-size: 0.9rem; border-left: 0; border-right: 0; }
    
    /* Info Box Enhancements */
    .info-box { min-height: 70px; border-radius: 8px; border-color: #f1f5f9 !important; }
    .info-box-icon { width: 45px; height: 45px; border-radius: 6px; font-size: 1.2rem; }
    .bg-primary-soft { background-color: #eef2ff; }
    .bg-success-soft { background-color: #ecfdf5; }
    .bg-warning-soft { background-color: #fffbeb; }
    .bg-danger-soft { background-color: #fef2f2; }
    
    /* Tabs styling */
    .nav-pills .nav-link { border-radius: 4px; color: #64748b; padding: 10px 15px; }
    .nav-pills .nav-link.active { background-color: #f1f5f9 !important; color: #0f172a !important; }
    
    /* Table Styling */
    .table-valign-middle td { vertical-align: middle !important; }
    .table thead th { border-top: 0; border-bottom: 1px solid #f1f5f9; letter-spacing: 0.5px; font-size: 10px; }
</style>
@endpush