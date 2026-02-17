@extends('layouts.app')

@section('title', 'Edit CPB')
@section('page-title', 'Edit CPB: ' . $cpb->batch_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cpb.index') }}">CPB</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cpb.show', $cpb) }}">{{ $cpb->batch_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Form Edit CPB</h3>
            </div>
            
            <form method="POST" action="{{ route('cpb.update', $cpb) }}">
                @csrf
                @method('PUT')
                
                <div class="card-body">
                    <div class="form-group">
                        <label>Nomor Batch</label>
                        <div class="form-control" style="background-color: #f8f9fa;">
                            <strong>{{ $cpb->batch_number }}</strong>
                        </div>
                        <small class="form-text text-muted">Nomor batch tidak dapat diubah</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Jenis CPB</label>
                        <div class="form-control" style="background-color: #f8f9fa;">
                            <strong>{{ ucfirst($cpb->type) }}</strong>
                        </div>
                        <small class="form-text text-muted">Jenis CPB tidak dapat diubah</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="product_name">Nama Produk *</label>
                        <input type="text" class="form-control @error('product_name') is-invalid @enderror" 
                               id="product_name" name="product_name" 
                               value="{{ old('product_name', $cpb->product_name) }}" required>
                        @error('product_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="schedule_duration">Durasi Rencana Produksi (jam) *</label>
                        <input type="number" class="form-control @error('schedule_duration') is-invalid @enderror" 
                               id="schedule_duration" name="schedule_duration" 
                               value="{{ old('schedule_duration', $cpb->schedule_duration) }}" 
                               min="1" max="720" required>
                        @error('schedule_duration')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label>Status Saat Ini</label>
                        <div class="form-control" style="background-color: #f8f9fa;">
                            {!! $cpb->status_badge !!}
                            <br>
                            <small>Sejak: {{ $cpb->entered_current_status_at->format('d/m/Y H:i') }}</small>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="icon fas fa-exclamation-triangle"></i> Perhatian:</h6>
                        <ul class="mb-0 pl-3">
                            <li>Anda hanya dapat mengedit CPB saat masih di status RND</li>
                            <li>Setelah diserahkan ke QA, CPB tidak dapat diedit lagi</li>
                            <li>Pastikan data sudah benar sebelum disimpan</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update CPB
                    </button>
                    <a href="{{ route('cpb.show', $cpb) }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi CPB</h3>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Dibuat Oleh:</dt>
                    <dd>{{ $cpb->creator->name ?? '-' }}</dd>
                    
                    <dt>Tanggal Dibuat:</dt>
                    <dd>{{ $cpb->created_at->format('d/m/Y H:i') }}</dd>
                    
                    <dt>Terakhir Diupdate:</dt>
                    <dd>{{ $cpb->updated_at->format('d/m/Y H:i') }}</dd>
                    
                    <dt>Lokasi Saat Ini:</dt>
                    <dd>{{ $cpb->currentDepartment->name ?? '-' }}</dd>
                </dl>
                
                @if($cpb->status == 'rnd')
                    <div class="alert alert-info">
                        <h6><i class="icon fas fa-info-circle"></i> Edit CPB:</h6>
                        <p class="mb-0">Anda dapat mengedit CPB karena masih dalam status RND</p>
                    </div>
                @else
                    <div class="alert alert-danger">
                        <h6><i class="icon fas fa-ban"></i> Tidak Dapat Edit:</h6>
                        <p class="mb-0">CPB sudah melewati tahap RND dan tidak dapat diedit lagi</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection