@extends('layouts.app')

@section('title', 'Edit CPB')
@section('page-title', 'Edit CPB: ' . $cpb->batch_number)

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-warning shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-edit mr-1"></i> Form Edit CPB</h3>
            </div>
            
            <form method="POST" action="{{ route('cpb.update', $cpb) }}">
                @csrf
                @method('PUT')
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nomor Batch</label>
                                <div class="form-control bg-light">
                                    <strong>{{ $cpb->batch_number }}</strong>
                                </div>
                                <small class="form-text text-muted">Nomor batch tidak dapat diubah</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jenis CPB</label>
                                <div class="form-control bg-light">
                                    <strong>{{ ucfirst($cpb->type) }}</strong>
                                </div>
                                <small class="form-text text-muted">Jenis CPB tidak dapat diubah</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mt-2">
                        <label for="product_name">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('product_name') is-invalid @enderror" 
                               id="product_name" name="product_name" 
                               value="{{ old('product_name', $cpb->product_name) }}" required>
                        @error('product_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- FITUR EDIT WAKTU CUSTOM SLA --}}
                    @php
                        // Cek apakah data CPB ini punya SLA Kustom sebelumnya
                        $customSlas = !empty($cpb->custom_slas) ? json_decode($cpb->custom_slas, true) : [];
                        $isCustomSlaEnabled = !empty($customSlas) || old('use_custom_time') == '1';
                    @endphp

                    <div class="form-group mt-4 pt-3 border-top">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="useCustomTime" name="use_custom_time" value="1" {{ $isCustomSlaEnabled ? 'checked' : '' }}>
                            <label class="custom-control-label text-muted" for="useCustomTime">
                                <i class="fas fa-stopwatch"></i> Atur Batas Waktu SLA Kustom untuk Setiap Departemen (Opsional)
                            </label>
                        </div>
                        
                        <div class="row mt-3 bg-light p-3 rounded border" id="customTimeWrapper" style="display: {{ $isCustomSlaEnabled ? 'flex' : 'none' }};">
                            <div class="col-md-12 mb-3">
                                <small class="text-info"><i class="fas fa-info-circle"></i> Ubah target waktu maksimal jika dokumen ini memerlukan prioritas/SLA khusus.</small>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-group">
                                    <label>RND</label>
                                    <input type="number" min="1" class="form-control custom-sla-input" name="sla[rnd]" value="{{ old('sla.rnd', $customSlas['rnd'] ?? 24) }}">
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-group">
                                    <label>QA Review</label>
                                    <input type="number" min="1" class="form-control custom-sla-input" name="sla[qa]" value="{{ old('sla.qa', $customSlas['qa'] ?? 24) }}">
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-group">
                                    <label>PPIC</label>
                                    <input type="number" min="1" class="form-control custom-sla-input" name="sla[ppic]" value="{{ old('sla.ppic', $customSlas['ppic'] ?? 4) }}">
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-group">
                                    <label>Warehouse</label>
                                    <input type="number" min="1" class="form-control custom-sla-input" name="sla[wh]" value="{{ old('sla.wh', $customSlas['wh'] ?? 24) }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="form-group">
                                    <label>Produksi</label>
                                    <input type="number" min="1" class="form-control custom-sla-input" name="sla[produksi]" value="{{ old('sla.produksi', $customSlas['produksi'] ?? 48) }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="form-group">
                                    <label>QC</label>
                                    <input type="number" min="1" class="form-control custom-sla-input" name="sla[qc]" value="{{ old('sla.qc', $customSlas['qc'] ?? 4) }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="form-group">
                                    <label>QA Final</label>
                                    <input type="number" min="1" class="form-control custom-sla-input" name="sla[qa_final]" value="{{ old('sla.qa_final', $customSlas['qa_final'] ?? 24) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- END FITUR WAKTU CUSTOM SLA --}}
                    
                    <div class="form-group mt-4 pt-3 border-top">
                        <label>Status Saat Ini</label>
                        <div class="form-control bg-light h-auto">
                            {!! $cpb->status_badge !!}
                            <span class="ml-2 text-muted"><i class="fas fa-clock"></i> Sejak: {{ $cpb->entered_current_status_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-4 mb-0">
                        <h6 class="font-weight-bold"><i class="icon fas fa-exclamation-triangle"></i> Perhatian:</h6>
                        <ul class="mb-0 pl-3 small">
                            <li>Anda hanya dapat mengedit CPB saat masih di status RND</li>
                            <li>Setelah diserahkan ke QA, CPB tidak dapat diedit lagi</li>
                            <li>Pastikan data sudah benar sebelum disimpan</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card-footer bg-white border-top">
                    <button type="submit" class="btn btn-warning px-4 font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('cpb.show', $cpb) }}" class="btn btn-light border px-4 ml-2">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold"><i class="fas fa-info-circle text-info mr-1"></i> Informasi Riwayat</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <span class="text-muted d-block small">Dibuat Oleh</span>
                        <strong>{{ $cpb->creator->name ?? '-' }}</strong>
                    </li>
                    <li class="list-group-item">
                        <span class="text-muted d-block small">Tanggal Dibuat</span>
                        <strong>{{ $cpb->created_at->format('d/m/Y H:i') }}</strong>
                    </li>
                    <li class="list-group-item">
                        <span class="text-muted d-block small">Terakhir Diupdate</span>
                        <strong>{{ $cpb->updated_at->format('d/m/Y H:i') }}</strong>
                    </li>
                    <li class="list-group-item">
                        <span class="text-muted d-block small">Lokasi Saat Ini</span>
                        <strong>{{ $cpb->currentDepartment->name ?? '-' }}</strong>
                    </li>
                </ul>
                
                <div class="p-3">
                    @if($cpb->status == 'rnd')
                        <div class="alert alert-info border-0 shadow-none mb-0 small">
                            <strong><i class="fas fa-unlock"></i> Bisa Diedit:</strong><br>
                            Dokumen masih berada di departemen Anda (RND).
                        </div>
                    @else
                        <div class="alert alert-danger border-0 shadow-none mb-0 small">
                            <strong><i class="fas fa-lock"></i> Terkunci:</strong><br>
                            CPB sudah melewati tahap RND dan tidak dapat diedit lagi.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Jalankan pengecekan di awal saat halaman dimuat
    toggleSlaRequired();

    // Event listener untuk switch toggle
    $('#useCustomTime').change(function() {
        if($(this).is(':checked')) {
            $('#customTimeWrapper').slideDown();
        } else {
            $('#customTimeWrapper').slideUp();
        }
        toggleSlaRequired();
    });

    function toggleSlaRequired() {
        if($('#useCustomTime').is(':checked')) {
            $('.custom-sla-input').attr('required', true); 
        } else {
            $('.custom-sla-input').removeAttr('required'); 
        }
    }
});
</script>
@endpush