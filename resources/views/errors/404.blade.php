@extends('layouts.app')

@section('title', '404 Not Found')
@section('page-title', '404 - Halaman Tidak Ditemukan')

@section('content')
<div class="error-page">
    <h2 class="headline text-warning"> 404</h2>

    <div class="error-content">
        <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Halaman tidak ditemukan.</h3>

        <p>
            Halaman yang Anda cari tidak dapat ditemukan.
            Kembali ke <a href="{{ route('dashboard') }}">dashboard</a> atau gunakan formulir pencarian.
        </p>

        <form class="search-form">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search">

                <div class="input-group-append">
                    <button type="submit" name="submit" class="btn btn-warning">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.error-page {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
    flex-direction: column;
    text-align: center;
}
.headline {
    font-size: 8rem;
    font-weight: 300;
    line-height: 1;
}
.error-content {
    margin-top: 2rem;
}
.search-form {
    max-width: 300px;
    margin: 2rem auto 0;
}
</style>
@endpush