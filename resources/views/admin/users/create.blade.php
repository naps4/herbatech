@extends('layouts.admin')

@section('admin-title', 'Tambah User Baru')
@section('admin-breadcrumb')
    <li class="breadcrumb-item active">Tambah User</li>
@endsection

@section('admin-content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Form Tambah User</h3>
            </div>
            
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nama Lengkap *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                        @error('password')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password *</label>
                        <input type="password" class="form-control" 
                               id="password_confirmation" name="password_confirmation" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select class="form-control @error('role') is-invalid @enderror" 
                                id="role" name="role" required>
                            <option value="">Pilih Role</option>
                            @foreach($roles as $key => $label)
                                <option value="{{ $key }}" {{ old('role') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Departemen</label>
                        <input type="text" class="form-control @error('department') is-invalid @enderror" 
                               id="department" name="department" value="{{ old('department') }}"
                               placeholder="Kosongkan untuk menggunakan default">
                        @error('department')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="text-muted">Departemen akan otomatis diisi sesuai role jika dikosongkan</small>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan User
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Role</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Penjelasan Role:</h5>
                    <ul class="mb-0">
                        <li><strong>Super Admin</strong>: Akses penuh ke semua fitur</li>
                        <li><strong>RND</strong>: Buat dan edit CPB sebelum ke QA</li>
                        <li><strong>QA</strong>: Review, terima, release CPB; akses semua data</li>
                        <li><strong>PPIC</strong>: Terima dari QA, serahkan ke WH</li>
                        <li><strong>Warehouse</strong>: Terima dari PPIC, serahkan ke Produksi</li>
                        <li><strong>Produksi</strong>: Isi data proses, serahkan ke QC & QA</li>
                        <li><strong>QC</strong>: Verifikasi data, kirim ke Produksi untuk finalisasi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection