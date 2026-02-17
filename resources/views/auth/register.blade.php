@extends('layouts.app')

@section('title', 'Register User')
@section('page-title', 'Register New User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Register User</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Form Pendaftaran User</h3>
            </div>
            
            <form method="POST" action="{{ route('register') }}">
                @csrf
                
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nama Lengkap *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
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
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Department *</label>
                        <input type="text" class="form-control @error('department') is-invalid @enderror" 
                               id="department" name="department" value="{{ old('department') }}" required>
                        @error('department')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Daftarkan User</button>
                    <a href="{{ route('dashboard') }}" class="btn btn-default">Batal</a>
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
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>Super Admin</strong> - Akses penuh ke semua fitur
                    </li>
                    <li class="list-group-item">
                        <strong>RND</strong> - Buat dan edit CPB sebelum QA
                    </li>
                    <li class="list-group-item">
                        <strong>QA</strong> - Review, terima, dan release CPB
                    </li>
                    <li class="list-group-item">
                        <strong>PPIC</strong> - Terima dari QA, serahkan ke WH
                    </li>
                    <li class="list-group-item">
                        <strong>WH</strong> - Terima dari PPIC, serahkan ke Produksi
                    </li>
                    <li class="list-group-item">
                        <strong>Produksi</strong> - Proses produksi, serahkan ke QC
                    </li>
                    <li class="list-group-item">
                        <strong>QC</strong> - Verifikasi quality, serahkan ke QA Final
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection