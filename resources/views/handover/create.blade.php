@extends('layouts.app')

@section('title', 'Serahkan CPB')
@section('page-title', 'Serahkan CPB: ' . $cpb->batch_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cpb.index') }}">CPB</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cpb.show', $cpb) }}">{{ $cpb->batch_number }}</a></li>
    <li class="breadcrumb-item active">Serahkan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">Form Serah Terima</h3>
            </div>
            
            <form method="POST" action="{{ route('handover.store', $cpb) }}">
                @csrf
                
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="icon fas fa-info-circle"></i> Informasi Handover</h6>
                        <p class="mb-0">
                            Anda akan menyerahkan CPB dari <strong>{{ ucfirst($cpb->status) }}</strong> 
                            ke <strong>{{ ucfirst($nextStatus) }}</strong>
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label>CPB yang Diserahkan</label>
                        <div class="form-control" style="background-color: #f8f9fa;">
                            <strong>{{ $cpb->batch_number }}</strong> - {{ $cpb->product_name }}
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Status Saat Ini</label>
                        <div class="form-control" style="background-color: #f8f9fa;">
                            {!! $cpb->status_badge !!}
                            <br>
                            <small>Durasi: {{ $cpb->duration_in_current_status }} jam</small>
                            @if($cpb->is_overdue)
                                <br><span class="text-danger"><i class="fas fa-exclamation-triangle"></i> OVERDUE!</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="receiver_id">Penerima *</label>
                        <select class="form-control @error('receiver_id') is-invalid @enderror" 
                                id="receiver_id" name="receiver_id" required>
                            <option value="">Pilih Penerima</option>
                            @foreach($nextUsers as $receiver) {{-- GANTI $receivers MENJADI $nextUsers --}}
                                <option value="{{ $receiver->id }}" {{ old('receiver_id') == $receiver->id ? 'selected' : '' }}>
                                    {{ $receiver->name }} - {{ $receiver->department }}
                                </option>
                            @endforeach
                        </select>
                        @error('receiver_id')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">Pilih user yang akan menerima CPB di departemen {{ ucfirst($nextStatus) }}</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Catatan (opsional)</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3" 
                                  placeholder="Tambahkan catatan mengenai handover ini...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <small class="form-text text-muted">Catatan akan tersimpan di log handover</small>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="confirm_handover" required>
                            <label class="custom-control-label" for="confirm_handover">
                                Saya menyatakan bahwa CPB ini siap untuk diserahkan ke departemen berikutnya
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-forward"></i> Konfirmasi Serah Terima
                    </button>
                    <a href="{{ route('cpb.show', $cpb) }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-6">
        <!-- CPB Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detail CPB</h3>
            </div>
            <div class="card-body">
                <dl>
                    <dt>No. Batch:</dt>
                    <dd>{{ $cpb->batch_number }}</dd>
                    
                    <dt>Jenis:</dt>
                    <dd>{{ ucfirst($cpb->type) }}</dd>
                    
                    <dt>Produk:</dt>
                    <dd>{{ $cpb->product_name }}</dd>
                    
                    <dt>Durasi Produksi:</dt>
                    <dd>{{ $cpb->schedule_duration }} jam</dd>
                    
                    <dt>Dibuat Oleh:</dt>
                    <dd>{{ $cpb->creator->name ?? 'N/A' }}</dd> {{-- TAMBAHKAN NULL CHECK --}}
                    
                    <dt>Tanggal Dibuat:</dt>
                    <dd>{{ $cpb->created_at->format('d/m/Y H:i') }}</dd>
                </dl>
            </div>
        </div>
        
        <!-- Time Tracking -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tracking Waktu</h3>
            </div>
            <div class="card-body">
                <div class="progress-group">
                    <span class="progress-text">Durasi di {{ ucfirst($cpb->status) }}</span>
                    <span class="float-right"><b>{{ $cpb->duration_in_current_status }}/{{ $cpb->time_limit }} jam</b></span>
                    <div class="progress progress-sm">
                        @php
                            $percentage = min(100, ($cpb->duration_in_current_status / $cpb->time_limit) * 100);
                            $color = $cpb->is_overdue ? 'danger' : ($percentage > 80 ? 'warning' : 'success');
                        @endphp
                        <div class="progress-bar bg-{{ $color }}" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
                
                <div class="callout callout-{{ $cpb->is_overdue ? 'danger' : 'info' }}">
                    <h5>Status Waktu</h5>
                    <p>
                        @if($cpb->is_overdue)
                            <i class="fas fa-exclamation-triangle text-danger"></i>
                            CPB telah OVERDUE sejak 
                            {{ $cpb->overdue_since ? $cpb->overdue_since->format('d/m/Y H:i') : 'N/A' }}
                        @elseif($cpb->time_remaining < ($cpb->time_limit * 0.2))
                            <i class="fas fa-exclamation-circle text-warning"></i>
                            Waktu tersisa: {{ $cpb->time_remaining }} jam
                        @else
                            <i class="fas fa-check-circle text-success"></i>
                            Waktu tersisa: {{ $cpb->time_remaining }} jam
                        @endif
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Handover Flow -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Alur Handover</h3>
            </div>
            <div class="card-body">
                <div class="timeline timeline-inverse">
                    @php
                        $flow = ['rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc', 'qa_final', 'released'];
                        $currentIndex = array_search($cpb->status, $flow);
                        if ($currentIndex === false) $currentIndex = -1;
                    @endphp
                    
                    @foreach($flow as $index => $status)
                        <div class="time-label">
                            @if($index == $currentIndex)
                                <span class="bg-success">
                                    <i class="fas fa-arrow-right"></i> {{ strtoupper($status) }}
                                </span>
                            @elseif($index < $currentIndex)
                                <span class="bg-secondary">
                                    <i class="fas fa-check"></i> {{ strtoupper($status) }}
                                </span>
                            @else
                                <span class="bg-light">
                                    {{ strtoupper($status) }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-select first receiver if only one
    if ($('#receiver_id option').length === 2) {
        $('#receiver_id').val($('#receiver_id option:eq(1)').val());
    }
});
</script>
@endpush