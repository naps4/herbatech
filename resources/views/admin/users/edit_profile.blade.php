@extends('layouts.app')

{{-- Kosongkan title agar tidak muncul di tab browser atau header --}}
@section('title', '')
@section('page-title', '')
@section('breadcrumb', '')


@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Profil Saya</h3>
    </div>
    <form action="{{ route('profile.update') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
            </div>
            <hr>
            <div class="form-group">
                <label>Password Baru (Kosongkan jika tidak ingin mengganti)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="form-group">
                <label>Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('profile.show') }}" class="btn btn-default">Batal</a>
        </div>
    </form>
</div>
@endsection