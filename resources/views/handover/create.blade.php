@extends('layouts.app')

@section('title', 'Serahkan CPB')
@section('page-title', 'Handover Batch: ' . $cpb->batch_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cpb.index') }}">CPB</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cpb.show', $cpb) }}">{{ $cpb->batch_number }}</a></li>
    <li class="breadcrumb-item active">Serahkan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="process-stepper d-flex justify-content-between align-items-center">
                    @php
                        $flow = ['rnd', 'qa', 'ppic', 'wh', 'produksi', 'qc', 'qa_final', 'released'];
                        $currentIndex = array_search($cpb->status, $flow);
                    @endphp

                    @foreach($flow as $index => $status)
                        <div class="step-item text-center {{ $index <= $currentIndex ? 'active' : '' }} {{ $index == $currentIndex ? 'current' : '' }}">
                            <div class="step-circle mx-auto">
                                @if($index < $currentIndex)
                                    <i class="fas fa-check"></i>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </div>
                            <div class="step-label mt-2 small font-weight-bold">{{ strtoupper(str_replace('_', ' ', $status)) }}</div>
                        </div>
                        @if(!$loop->last)
                            <div class="step-line"></div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card card-success card-outline shadow">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-exchange-alt mr-1 text-success"></i> Konfirmasi Pengiriman Dokumen
                </h3>
            </div>
            
            <form method="POST" action="{{ route('handover.store', $cpb) }}">
                @csrf
                <div class="card-body">
                    @if($cpb->is_overdue)
                        <div class="alert alert-danger shadow-sm mb-4">
                            <h5><i class="icon fas fa-exclamation-triangle"></i> PERHATIAN: BATCH OVERDUE</h5>
                            <p class="mb-0">Batch ini telah lebih dari {{ $cpb->time_limit }} jam. 
                            <strong>Anda wajib mencantumkan alasan pada kolom catatan.</strong></p>
                        </div>
                    @endif

                    <div class="callout callout-success mb-4">
                        <h5>Akan dikirim ke bagian: <strong>{{ strtoupper(str_replace('_', ' ', $nextStatus)) }}</strong></h5>
                        <p class="mb-0 text-muted small">Pastikan dokumen fisik dan sistem sudah sesuai sebelum diserahkan.</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="receiver_id">Pilih Personel Penerima ({{ strtoupper(str_replace('_', ' ', $nextStatus)) }}) <span class="text-danger">*</span></label>
                        <select class="form-control select2 @error('receiver_id') is-invalid @enderror" 
                                id="receiver_id" name="receiver_id" required>
                            <option value="">-- Pilih Nama Penerima --</option>
                            @foreach($nextUsers as $receiver)
                                <option value="{{ $receiver->id }}" {{ old('receiver_id') == $receiver->id ? 'selected' : '' }}>
                                    {{ $receiver->name }} ({{ strtoupper($receiver->role) }})
                                </option>
                            @endforeach
                        </select>
                        @if(count($nextUsers) == 0)
                            <span class="text-danger small"><i class="fas fa-info-circle"></i> Tidak ada user ditemukan dengan role "{{ $nextStatus }}". Silahkan hubungi Admin.</span>
                        @endif
                        @error('receiver_id')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">
                            Catatan Handover 
                            @if($cpb->is_overdue) 
                                <span class="text-danger font-weight-bold">* (Wajib diisi karena status Overdue)</span> 
                            @else 
                                <span class="text-muted small">(Opsional)</span>
                            @endif
                        </label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3" 
                                  {{ $cpb->is_overdue ? 'required' : '' }}
                                  placeholder="{{ $cpb->is_overdue ? 'Sebutkan alasan keterlambatan...' : 'Contoh: Dokumen lengkap, sisa bahan di gudang B...' }}">{{ old('notes') }}</textarea>
                        @error('notes')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    
                    <div class="p-3 bg-light border rounded mt-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="confirm_handover" required>
                            <label class="custom-control-label font-weight-normal" for="confirm_handover">
                                Saya bertanggung jawab penuh atas kebenaran data CPB ini saat diserahkan ke bagian {{ strtoupper(str_replace('_', ' ', $nextStatus)) }}.
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-white text-right">
                    <a href="{{ route('cpb.show', $cpb) }}" class="btn btn-link text-muted mr-2">Batal</a>
                    <button type="submit" class="btn btn-success px-4 shadow-sm">
                        <i class="fas fa-paper-plane mr-1"></i> Serah Terima CPB
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Ringkasan Batch</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <th class="p-3 bg-light" style="width: 40%">No. Batch</th>
                            <td class="p-3"><strong>{{ $cpb->batch_number }}</strong></td>
                        </tr>
                        <tr>
                            <th class="p-3 bg-light">Produk</th>
                            <td class="p-3">{{ $cpb->product_name }}</td>
                        </tr>
                        <tr>
                            <th class="p-3 bg-light">Durasi Proses</th>
                            <td class="p-3">
                                <span class="{{ $cpb->is_overdue ? 'text-danger font-weight-bold' : 'text-success' }}">
                                    {{ $cpb->duration_in_current_status }} / {{ $cpb->time_limit }} jam
                                </span>
                                @if($cpb->is_overdue)
                                    <span class="badge badge-danger ml-1">OVERDUE</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="p-3 bg-light">Status Saat Ini</th>
                            <td class="p-3">{!! $cpb->status_badge !!}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Stepper Styling */
    .process-stepper { position: relative; }
    .step-item { z-index: 2; flex: 1; }
    .step-circle {
        width: 32px; height: 32px; border-radius: 50%;
        background-color: #e9ecef; color: #adb5bd;
        display: flex; align-items: center; justify-content: center;
        border: 2px solid #fff; box-shadow: 0 0 0 1px #dee2e6;
        transition: all 0.3s; font-size: 0.8rem;
    }
    .step-line { height: 2px; background-color: #dee2e6; flex: 1; margin-top: -24px; }
    .step-item.active .step-circle { background-color: #28a745; color: white; box-shadow: 0 0 0 1px #28a745; }
    .step-item.current .step-circle { 
        background-color: #ffc107; color: #212529; 
        box-shadow: 0 0 0 1px #ffc107; transform: scale(1.1); 
    }
    .step-item.active .step-label { color: #28a745; }
    .step-item.current .step-label { color: #212529; }
</style>
@endpush