@extends('layouts.admin')

@section('admin-title', 'User Management')
@section('page-title', '')

@section('admin-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">User Management</li>
@endsection

@section('admin-content')
<div class="container-fluid px-md-4"> {{-- Tambah padding horizontal untuk kenyamanan mata --}}
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="d-flex flex-wrap text-center">
                        <div class="flex-fill p-3 border-right border-left-primary">
                            <span class="text-uppercase tracking-wider text-muted small d-block mb-1">Total Users</span>
                            <h4 class="font-weight-bold mb-0 text-dark">{{ $totalUsers }}</h4>
                        </div>
                        @foreach(['rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc'] as $role)
                        <div class="flex-fill p-3 border-right d-none d-lg-block">
                            <span class="text-uppercase tracking-wider text-muted small d-block mb-1">{{ $role }}</span>
                            <h4 class="font-weight-bold mb-0 text-secondary">{{ $roleCounts[$role] ?? 0 }}</h4>
                        </div>
                        @endforeach
                        <div class="p-3 bg-light d-flex align-items-center justify-content-center px-4">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-success btn-sm px-4 font-weight-bold shadow-sm" style="color: white !important;">
                                <i class="fas fa-plus-circle mr-2" style="color: white !important;"></i> TAMBAH USER
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3 class="card-title font-weight-bold text-dark mb-0">
                                <span class="bg-primary-soft p-2 rounded mr-2">
                                    <i class="fas fa-users-cog text-primary"></i>
                                </span>
                                Database Pengguna PT
                            </h3>
                        </div>
                        
                        <div class="col-md-6 mt-3 mt-md-0">
                            <form method="GET" class="d-flex justify-content-md-end">
                                <div class="input-group input-group-sm mr-2" style="width: 150px;">
                                    <select name="role" class="form-control custom-select border-soft" onchange="this.form.submit()">
                                        <option value="all">Semua Role</option>
                                        @foreach(['superadmin', 'rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc'] as $r)
                                            <option value="{{ $r }}" {{ request('role') == $r ? 'selected' : '' }}>{{ strtoupper($r) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="input-group input-group-sm" style="width: 220px;">
                                    <input type="text" name="search" class="form-control border-soft" placeholder="Cari nama/email..." value="{{ request('search') }}">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary px-3">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                @if(request()->filled('search') || (request()->has('role') && request('role') != 'all'))
                                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-link text-muted ml-1" title="Clear Filter">
                                        <i class="fas fa-sync-alt"></i>
                                    </a>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border-0">
                            <thead>
                                <tr>
                                    <th class="pl-4 py-3 text-muted small font-weight-bold border-0">#</th>
                                    <th class="py-3 text-muted small font-weight-bold border-0">IDENTITAS PENGGUNA</th>
                                    <th class="py-3 text-muted small font-weight-bold border-0">AKSES & DEPARTEMEN</th>
                                    <th class="py-3 text-muted small font-weight-bold border-0 text-center">TGL DAFTAR</th>
                                    <th class="py-3 text-muted small font-weight-bold border-0 text-center pr-4">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr class="{{ auth()->id() == $user->id ? 'bg-active-user' : '' }}">
                                    <td class="pl-4 align-middle text-muted small font-weight-bold">
                                        {{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center py-1">
                                            <div class="avatar-corp mr-3 shadow-xs">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-weight-bold text-dark text-sm mb-0">{{ $user->name }}</div>
                                                <div class="text-muted text-xs font-italic">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="mb-1">{!! $user->role_badge !!}</div>
                                        <span class="text-xs text-uppercase tracking-wider text-muted font-weight-bold">
                                            {{ $user->department ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="text-sm font-weight-bold text-dark">{{ $user->created_at->format('d/m/Y') }}</div>
                                        <div class="text-xs text-muted">{{ $user->created_at->format('H:i') }} WIB</div>
                                    </td>
                                    <td class="text-center align-middle pr-4">
                                        <div class="btn-group shadow-xs rounded border overflow-hidden">
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-white btn-xs px-3" data-toggle="tooltip" title="Profil">
                                                <i class="fas fa-id-badge text-primary"></i>
                                            </a>
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-white btn-xs px-3 border-left border-right" data-toggle="tooltip" title="Edit">
                                                <i class="fas fa-edit text-dark"></i>
                                            </a>
                                            @if(auth()->id() != $user->id)
                                            <button type="button" class="btn btn-white btn-xs px-3" 
                                                    onclick="confirmDelete('{{ $user->id }}', '{{ $user->name }}')" 
                                                    data-toggle="tooltip" title="Hapus">
                                                <i class="fas fa-trash-alt text-danger"></i>
                                            </button>
                                            <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-none">
                                                @csrf @method('DELETE')
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="fas fa-user-slash fa-3x text-light mb-3"></i>
                                            <p class="text-muted font-italic">Tidak ditemukan data pengguna dalam sistem.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card-footer bg-light border-top py-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                        <div class="text-xs text-muted mb-2 mb-md-0 font-weight-bold text-uppercase">
                            Record {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} User Terdaftar
                        </div>
                        <div class="pagination-sm">
                            {{ $users->withQueryString()->links() }}
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
    /* PT Typography & Consistency */
    body { font-family: 'Inter', 'Segoe UI', Roboto, sans-serif; }
    .tracking-wider { letter-spacing: 1px; }
    .text-xs { font-size: 0.75rem; }
    .text-sm { font-size: 0.875rem; }
    
    /* PT Colors & Accents */
    .bg-active-user { background-color: #f0f7ff !important; }
    .bg-primary-soft { background-color: #eef2ff; }
    .border-soft { border-color: #e2e8f0 !important; }
    .border-left-primary { border-left: 4px solid #007bff !important; }
    
    /* Professional Avatar */
    .avatar-corp { 
        width: 40px; 
        height: 40px; 
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px; /* Kotak rounded lebih profesional daripada bulat sempurna */
    }
    
    /* Table Enhancements */
    .table thead th { 
        background-color: #fbfcfd;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #edf2f7 !important;
    }
    .table tbody tr:hover { background-color: #f8fafc; }
    
    /* Action Buttons */
    .btn-white { background: #fff; color: #475569; }
    .btn-white:hover { background: #f1f5f9; }
    .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }

    /* Custom Scrollbar for Table */
    .table-responsive::-webkit-scrollbar { height: 6px; }
    .table-responsive::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>
@endpush

@push('scripts')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: "Apakah Anda yakin ingin menghapus data " + name + "?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endpush