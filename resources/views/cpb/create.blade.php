@extends('layouts.app')

@section('title', 'Buat CPB Baru')
@section('page-title', 'Buat CPB Baru')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cpb.index') }}">CPB</a></li>
    <li class="breadcrumb-item active">Buat Baru</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-dark shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title text-dark font-weight-bold">
                        <i class="fas fa-file-medical mr-1 text-dark"></i> Form Input CPB Baru
                    </h3>
                </div>
                
                <form method="POST" action="{{ route('cpb.store') }}" enctype="multipart/form-data" id="formCreateCPB">
                    @csrf
                    
                    <div class="card-body">
                        <div class="mb-4">
                            <h6 class="text-dark border-bottom pb-2 mb-3 font-weight-bold">
                                <i class="fas fa-barcode mr-1"></i> Identitas Batch
                            </h6>
                            <div class="form-group">
                                <label for="batch_number">Nomor Batch <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-dark text-white"><i class="fas fa-tag"></i></span>
                                    </div>
                                    <input type="text" class="form-control @error('batch_number') is-invalid @enderror" 
                                           id="batch_number" name="batch_number" value="{{ old('batch_number') }}" 
                                           placeholder="Otomatis terisi setelah pilih jenis" required readonly>
                                </div>
                                @error('batch_number')
                                    <span class="text-danger small"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="type">Jenis CPB <span class="text-danger">*</span></label>
                                        <select class="form-control select2bs4 @error('type') is-invalid @enderror" 
                                                id="type" name="type" required>
                                            <option value="">-- Pilih Jenis --</option>
                                            <option value="pengolahan" {{ old('type') == 'pengolahan' ? 'selected' : '' }}>Pengolahan</option>
                                            <option value="pengemasan" {{ old('type') == 'pengemasan' ? 'selected' : '' }}>Pengemasan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="product_name">Nama Produk <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('product_name') is-invalid @enderror" 
                                               id="product_name" name="product_name" value="{{ old('product_name') }}" 
                                               placeholder="Masukkan nama lengkap produk" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-dark border-bottom pb-2 mb-3 font-weight-bold">
                                <i class="fas fa-paperclip mr-1"></i> Dokumen Pendukung
                            </h6>
                            <div class="form-group">
                                <label for="product_name">Nama Produk *</label>
                                <input type="text" class="form-control @error('product_name') is-invalid @enderror" 
                                       id="product_name" name="product_name" value="{{ old('product_name') }}" 
                                       placeholder="Nama produk" required>
                                @error('product_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="attachment">Lampiran Dokumen *</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('file') is-invalid @enderror" 
                                       id="file" name="file" required>
                                <label class="custom-file-label" for="file">Pilih file...</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Informasi Pembuat</label>
                        <div class="form-control" style="background-color: #f8f9fa;">
                            <strong>Pembuat:</strong> {{ auth()->user()->name }}<br>
                            <strong>Departemen:</strong> {{ auth()->user()->department }}<br>
                            <strong>Tanggal:</strong> {{ now()->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan CPB
                    </button>
                    <a href="{{ route('cpb.index') }}" class="btn btn-default">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi</h3>
            </div>
            <div class="card-body">
                <h5>Alur CPB:</h5>
                <ol class="pl-3">
                    <li><strong>RND</strong> - Pembuatan CPB (Real-time)</li>
                    <li><strong>QA</strong> - Review awal (Real-time)</li>
                    <li><strong>PPIC</strong> - Perencanaan (Real-time)</li>
                    <li><strong>Warehouse</strong> - Penyiapan bahan (Real-time)</li>
                    <li><strong>Produksi</strong> - Proses produksi (Real-time)</li>
                    <li><strong>QC</strong> - Quality control (Real-time)</li>
                    <li><strong>QA Final</strong> - Final approval (Real-time)</li>
                    <li><strong>Released</strong> - CPB selesai</li>
                </ol>
                
                <div class="alert alert-info mt-3">
                    <h6><i class="icon fas fa-info-circle"></i> Catatan:</h6>
                    <ul class="mb-0 pl-3">
                        <li>CPB akan otomatis masuk ke antrian QA setelah disimpan</li>
                        <li>Notifikasi akan dikirim ke departemen QA</li>
                        <li>Waktu dihitung secara <strong>Real-time</strong> sejak masuk departemen</li>
                        <li>Tidak ada batasan waktu (Unlimited)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-generate batch number suggestion
    $('#type').change(function() {
        if (!$(this).val()) return;
        
        const year = new Date().getFullYear();
        const typeCode = $(this).val() === 'pengolahan' ? 'P' : 'K';
        
        $.get('/api/cpb/last-number?type=' + $(this).val(), function(data) {
            const nextNumber = data.last_number + 1;
            const batchNumber = `CPB-${year}-${typeCode}${nextNumber.toString().padStart(3, '0')}`;
            
            $('#batch_number').val(batchNumber);
        }).fail(function() {
            const batchNumber = `CPB-${year}-${typeCode}001`;
            $('#batch_number').val(batchNumber);
        });
    });
});
</script>
@endpush