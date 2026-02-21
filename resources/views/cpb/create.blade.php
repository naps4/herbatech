@extends('layouts.app')

{{-- Bagian ini dikosongkan atau dihapus agar tidak muncul --}}
@section('title', '') 
@section('page-title', '')
@section('breadcrumb', '')



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
                            <div class="row">
                                <!-- <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="file">Lampiran Dokumen <span class="text-danger">*</span></label>
                                        <div class="custom-file">
                                            <input type="file" 
                                                class="custom-file-input @error('file') is-invalid @enderror" 
                                                id="file" 
                                                name="file" 
                                                required> <label class="custom-file-label" for="file">Pilih file (Wajib)...</label>
                                        </div>
                                        <small class="text-info">
                                            <i class="fas fa-info-circle"></i> Dokumen wajib dilampirkan agar tombol "Simpan & Teruskan" aktif.
                                        </small>
                                        @error('file')
                                            <span class="text-danger small"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div> -->
                                <div class="col-md-6">
                                <div class="form-group">
                                    <label>No. Batch</label>
                                    <input type="text" id="batch_number" name="batch_number" class="form-control" readonly required>
                                </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="type">Jenis CPB <span class="text-danger">*</span></label>
                                        <select class="form-control select2bs4 @error('type') is-invalid @enderror" 
                                                id="type" name="type" required>
                                            <option value="">-- Pilih Jenis --</option>
                                            <option value="pengolahan" {{ old('type') == 'pengolahan' ? 'selected' : '' }}>Pengolahan</option>
                                            <option value="pengemasan" {{ old('type') == 'pengemasan' ? 'selected' : '' }}>Pengemasan</option>
                                        </select>
                                        @error('type')
                                            <span class="text-danger small"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="product_name">Nama Produk <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('product_name') is-invalid @enderror" 
                                               id="product_name" name="product_name" value="{{ old('product_name') }}" 
                                               placeholder="Masukkan nama lengkap produk" required>
                                        @error('product_name')
                                            <span class="text-danger small"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="schedule_duration">Target Durasi (Jam) <span class="text-danger">*</span></label>
                                    <input type="number" name="schedule_duration" id="schedule_duration" 
                                        class="form-control @error('schedule_duration') is-invalid @enderror" 
                                        value="{{ old('schedule_duration', 24) }}" required>
                                    <small class="text-muted">Lama waktu pengerjaan yang direncanakan.</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="text-dark border-bottom pb-2 mb-3 font-weight-bold">
                                <i class="fas fa-paperclip mr-1"></i> Dokumen Pendukung
                            </h6>
                            <div class="form-group">
                                <label for="file">Lampiran Dokumen <span class="text-danger">*</span></label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('file') is-invalid @enderror" 
                                           id="file" name="file" required>
                                    <label class="custom-file-label" for="file">Pilih file...</label>
                                </div>
                                <small class="text-muted">Format: PDF, JPG, PNG (Maks. 5MB)</small>
                                @error('file')
                                    <br><span class="text-danger small"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="text-muted">Informasi Pembuat</label>
                            <div class="p-3 border rounded shadow-sm" style="background-color: #f8f9fa;">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <p class="mb-1"><strong><i class="fas fa-user mr-1"></i> Pembuat:</strong> {{ auth()->user()->name }}</p>
                                        <p class="mb-0"><strong><i class="fas fa-building mr-1"></i> Departemen:</strong> {{ auth()->user()->department }}</p>
                                    </div>
                                    <div class="col-sm-6 text-sm-right">
                                        <p class="mb-0 text-muted small"><strong><i class="fas fa-calendar-alt mr-1"></i> Tanggal:</strong> {{ now()->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i> Simpan CPB
                        </button>
                        <a href="{{ route('cpb.index') }}" class="btn btn-link text-muted">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-info-circle mr-1"></i> Panduan Alur</h3>
                </div>
                <div class="card-body">
                    <div class="timeline timeline-inverse mb-0">
                        @php
                            $steps = ['RND (Input)', 'QA (Review)', 'PPIC', 'Warehouse', 'Produksi', 'QC', 'QA Final', 'Released'];
                        @endphp
                        @foreach($steps as $index => $step)
                        <div>
                            <i class="fas {{ $index == 0 ? 'fa-edit bg-primary' : 'fa-arrow-down bg-gray' }}"></i>
                            <div class="timeline-item shadow-none border-0 pb-2">
                                <span class="time"><i class="fas fa-clock"></i> Real-time</span>
                                <h3 class="timeline-header border-0"><strong>{{ $step }}</strong></h3>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="alert alert-light border mt-4 mb-0">
                        <h6 class="font-weight-bold text-info"><i class="fas fa-lightbulb mr-1"></i> Catatan Penting:</h6>
                        <ul class="mb-0 pl-3 small">
                            <li>Otomatis masuk antrian QA setelah disimpan.</li>
                            <li>Notifikasi akan dikirim ke departemen terkait secara otomatis.</li>
                            <li>Waktu dihitung sejak CPB diterima oleh departemen tersebut.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Menampilkan nama file pada input custom-file
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Auto-generate batch number
    $('#type').change(function() {
        const type = $(this).val();
        if (!type) {
            $('#batch_number').val('');
            return;
        }
        
        const year = new Date().getFullYear();
        const typeCode = type === 'pengolahan' ? 'P' : 'K';
        
        // Menggunakan url() agar alamatnya lengkap (http://127.0.0.1:8000/api/...)
        const targetUrl = "{{ route('cpb.last-number') }}";
        
        $.get(targetUrl, { type: type }, function(data) {
            console.log("Data diterima:", data); 
            const nextNumber = (parseInt(data.last_number) || 0) + 1;
            const batchNumber = `CPB-${year}-${typeCode}${nextNumber.toString().padStart(3, '0')}`;
            $('#batch_number').val(batchNumber);
        }).fail(function(xhr) {
            console.error("Error Detail:", xhr.status);
            // Jika masih 404, kita beri nomor manual sementara
            $('#batch_number').val(`CPB-${year}-${typeCode}001`);
        });
    });
});

$(document).ready(function() {
    const form = $('#formCreateCPB');
    const submitBtn = form.find('button[type="submit"]');
    const fileInput = $('#file');

    // Validasi saat form dikirim
    form.on('submit', function(e) {
        if (fileInput.get(0).files.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Dokumen Belum Ada',
                text: 'Harap lampirkan dokumen pendukung sebelum melanjutkan ke tahap berikutnya.'
            });
            return false;
        }
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
    });
});
</script>
@endpush