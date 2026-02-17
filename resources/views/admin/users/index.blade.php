@extends('layouts.admin')

@section('admin-title', 'User Management')
@section('admin-breadcrumb')
    <li class="breadcrumb-item active">Semua User</li>
@endsection

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar User</h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah User Baru
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="GET" class="form-inline">
                            <div class="form-group mr-2">
                                <select name="role" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <option value="all" {{ request('role') == 'all' ? 'selected' : '' }}>Semua Role</option>
                                    <option value="superadmin" {{ request('role') == 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                                    <option value="rnd" {{ request('role') == 'rnd' ? 'selected' : '' }}>RND</option>
                                    <option value="qa" {{ request('role') == 'qa' ? 'selected' : '' }}>QA</option>
                                    <option value="ppic" {{ request('role') == 'ppic' ? 'selected' : '' }}>PPIC</option>
                                    <option value="wh" {{ request('role') == 'wh' ? 'selected' : '' }}>Warehouse</option>
                                    <option value="produksi" {{ request('role') == 'produksi' ? 'selected' : '' }}>Produksi</option>
                                    <option value="qc" {{ request('role') == 'qc' ? 'selected' : '' }}>QC</option>
                                </select>
                            </div>
                            <div class="form-group mr-2">
                                <input type="text" name="search" class="form-control form-control-sm" 
                                       placeholder="Cari nama/email..." value="{{ request('search') }}">
                            </div>
                            <button type="submit" class="btn btn-sm btn-default mr-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                        </form>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row mb-3">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $totalUsers }}</h3>
                                <p>Total Users</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    @foreach(['rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc'] as $role)
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-light">
                            <div class="inner">
                                <h3>{{ $roleCounts[$role] ?? 0 }}</h3>
                                <p>{{ strtoupper($role) }} Users</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-tag"></i>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Departemen</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    @if(auth()->id() == $user->id)
                                        <span class="badge badge-info">Anda</span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{!! $user->role_badge !!}</td>
                                <td>{{ $user->department }}</td>
                                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if(auth()->id() != $user->id)
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" 
                                              onsubmit="return confirm('Hapus user {{ $user->name }}?')" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data user.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-3">
                    {{ $users->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection