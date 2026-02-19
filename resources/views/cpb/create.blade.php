@extends('layouts.app')

@section('page-title')
    <div class="d-flex align-items-center">
@endsection

@section('breadcrumb')
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
                                <label for="fileInput">Upload Dokumen Awal (PDF/IMG) <span class="text-danger">*</span></label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('file') is-invalid @enderror" 
                                           id="fileInput" name="file" required onchange="handleFileSelect(this)">
                                    <label class="custom-file-label" for="fileInput">Pilih berkas...</label>
                                </div>
                                
                                <div id="fileInfoPreview" class="mt-3 d-none">
                                    <div class="alert bg-white d-flex align-items-center mb-0 py-2 shadow-sm border" style="border-color: #343a40 !important; border-left: 4px solid #343a40 !important;">
                                        <div class="mr-3 text-dark">
                                            <i class="fas fa-file-pdf fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p id="previewName" class="mb-0 font-weight-bold text-dark small text-truncate" style="max-width: 250px;"></p>
                                            <p id="previewSize" class="mb-0 text-muted small"></p>
                                        </div>
                                        <div class="ml-auto">
                                            <button type="button" class="btn btn-xs btn-outline-danger border-0" onclick="resetFileSelection()" title="Hapus file">
                                                <i class="fas fa-times-circle"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                @error('file')
                                    <span class="text-danger small d-block mt-1"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="callout callout-info mb-0">
                            <div class="row">
                                <div class="col-sm-4">
                                    <p class="text-muted mb-0 small text-uppercase font-weight-bold">Pendaftar</p>
                                    <p class="mb-0 font-weight-bold">{{ auth()->user()->name }}</p>
                                </div>
                                <div class="col-sm-4 border-left">
                                    <p class="text-muted mb-0 small text-uppercase font-weight-bold">Departemen</p>
                                    <p class="mb-0 font-weight-bold">{{ auth()->user()->department ?? 'R&D' }}</p>
                                </div>
                                <div class="col-sm-4 border-left">
                                    <p class="text-muted mb-0 small text-uppercase font-weight-bold">Waktu Server</p>
                                    <p class="mb-0 font-weight-bold">{{ now()->format('d M Y, H:i') }} WIB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-white text-right">
                        <button type="submit" class="btn btn-dark px-4 shadow-sm">
                            <i class="fas fa-save mr-1"></i> Daftarkan CPB
                        </button>
                        <a href="{{ route('cpb.index') }}" class="btn btn-link text-muted">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h3 class="card-title small-caps font-weight-bold"><i class="fas fa-project-diagram mr-2"></i> Visualisasi Alur</h3>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush small">
                        <div class="list-group-item list-group-item-action bg-light font-weight-bold">
                            <i class="fas fa-circle text-primary mr-2"></i> 1. RND (Tahap Sekarang)
                        </div>
                        <div class="list-group-item"><i class="fas fa-arrow-right text-muted mr-2"></i> 2. QA Review</div>
                        <div class="list-group-item"><i class="fas fa-arrow-right text-muted mr-2"></i> 3. PPIC</div>
                        <div class="list-group-item"><i class="fas fa-arrow-right text-muted mr-2"></i> 4. Prod & WH</div>
                        <div class="list-group-item"><i class="fas fa-arrow-right text-muted mr-2"></i> 5. QA Final</div>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning border-0 shadow-sm mt-3">
                <h6 class="font-weight-bold text-dark"><i class="icon fas fa-exclamation-triangle"></i> Penting!</h6>
                <p class="small mb-0 text-dark">Sistem menggunakan penghitungan waktu <strong>Real-time</strong> sejak batch didaftarkan.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>

$('#type').on('change', function() {
    const type = $(this).val();
    const batchInput = $('#batch_number');
    
    if (!type) {
        batchInput.val('');
        return;
    }
    
    const year = new Date().getFullYear();
    const typeCode = (type === 'pengolahan') ? 'P' : 'K';
    
    batchInput.val('Checking...');

    $.ajax({
        url: '/api/cpb/last-number', // Pastikan route API sudah benar
        type: 'GET',
        data: { type: type },
        success: function(response) {
            // Ambil angka terakhir dari response, tambahkan 1
            const nextNumber = (parseInt(response.last_number) || 0) + 1;
            
            // Format padding 3 digit (001, 002, dst)
            const formattedNumber = nextNumber.toString().padStart(3, '0');
            const newBatchNumber = `CPB-${year}-${typeCode}${formattedNumber}`;
            
            batchInput.val(newBatchNumber);
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            // Fallback jika error (misal koneksi putus)
            batchInput.val(`CPB-${year}-${typeCode}001`);
        }
    });
});

/**
 * Fungsi untuk menangani pemilihan file dan menampilkan preview informasi
 */
function handleFileSelect(input) {
    const previewContainer = document.getElementById('fileInfoPreview');
    const nameLabel = document.getElementById('previewName');
    const sizeLabel = document.getElementById('previewSize');
    const inputLabel = document.querySelector('.custom-file-label');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        nameLabel.textContent = file.name;
        inputLabel.textContent = file.name;
        
        // Konversi ukuran file ke KB atau MB
        const fileSize = (file.size / 1024).toFixed(2);
        if (fileSize > 1024) {
            sizeLabel.textContent = (fileSize / 1024).toFixed(2) + ' MB';
        } else {
            sizeLabel.textContent = fileSize + ' KB';
        }
        
        previewContainer.classList.remove('d-none');
    } else {
        resetFileSelection();
    }
}

/**
 * Fungsi untuk mereset pilihan file
 */
function resetFileSelection() {
    const fileInput = document.getElementById('fileInput');
    const previewContainer = document.getElementById('fileInfoPreview');
    const inputLabel = document.querySelector('.custom-file-label');

    fileInput.value = ""; // Bersihkan nilai input
    inputLabel.textContent = "Pilih berkas...";
    previewContainer.classList.add('d-none');
}
</script>
@endpush