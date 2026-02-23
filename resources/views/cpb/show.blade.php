@extends('layouts.app')

@section('title', 'Detail CPB: ' . $cpb->batch_number)
@section('page-title', 'Detail CPB: ' . $cpb->batch_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cpb.index') }}">CPB</a></li>
    <li class="breadcrumb-item active">{{ $cpb->batch_number }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        {{-- Card Informasi Utama --}}
        <div class="card shadow-sm border-light">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold mb-0">Informasi Batch</h3>
                <div class="card-tools">
                    @if($cpb->status == 'rnd' && auth()->user()->id == $cpb->created_by)
                        <a href="{{ route('cpb.edit', $cpb) }}" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-edit mr-1"></i> Edit Data
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($cpb->is_rework)
                    <div class="alert alert-warning border-0 shadow-sm mb-4">
                        <div class="d-flex">
                            <i class="icon fas fa-exclamation-triangle mr-3 mt-1 text-danger"></i>
                            <div>
                                <h5 class="font-weight-bold">Status: Perbaikan (Rework)</h5>
                                <p class="mb-0 font-italic text-dark">"{{ $cpb->rework_note }}"</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6 border-right">
                        <dl class="row mb-0">
                            <dt class="col-sm-5 text-muted">No. Batch</dt>
                            <dd class="col-sm-7 font-weight-bold text-lg">{{ $cpb->batch_number }}</dd>
                            
                            <dt class="col-sm-5 text-muted">Jenis</dt>
                            <dd class="col-sm-7">
                                <span class="badge {{ $cpb->type == 'pengolahan' ? 'badge-info' : 'badge-primary' }} px-2 py-1 text-uppercase">
                                    {{ $cpb->type }}
                                </span>
                            </dd>

                            <dt class="col-sm-5 text-muted">Produk</dt>
                            <dd class="col-sm-7">{{ $cpb->product_name }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-5 text-muted">Tahapan</dt>
                            <dd class="col-sm-7">{!! $cpb->status_badge !!}</dd>

                            <dt class="col-sm-5 text-muted">Pemegang</dt>
                            <dd class="col-sm-7 text-truncate">
                                <strong>{{ $cpb->currentDepartment->name ?? '-' }}</strong>
                            </dd>

                            <dt class="col-sm-5 text-muted">Efisiensi</dt>
                            <dd class="col-sm-7">{!! $cpb->time_status_badge !!}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Lampiran Dokumen --}}
        <div class="card shadow-sm border-light">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold mb-0">Dokumen Lampiran</h3>
                @if(!$hasAttachment && (auth()->user()->role === $cpb->status || auth()->user()->isSuperAdmin()) && $cpb->status !== 'released')
                    <span class="badge badge-danger"><i class="fas fa-exclamation-circle mr-1"></i> Wajib Upload Dokumen</span>
                @endif
            </div>
            <div class="card-body">
                @if($cpb->attachments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th>File</th>
                                    <th>Keterangan</th>
                                    <th>Diunggah Oleh</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cpb->attachments as $attachment)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-alt text-primary fa-lg mr-2"></i>
                                                <span class="text-sm font-weight-medium text-dark">{{ $attachment->file_name }}</span>
                                            </div>
                                        </td>
                                        <td class="text-sm text-muted">{{ $attachment->description ?? 'Tidak ada keterangan' }}</td>
                                        <td class="text-sm">
                                            <span class="badge badge-light border">{{ $attachment->uploader->name }}</span>
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ Storage::url($attachment->file_path) }}" class="btn btn-sm btn-outline-primary" download title="Unduh File">
                                                <i class="fas fa-download"></i>
                                            </a>

                                            {{-- LOGIKA: Izin Hapus Dokumen --}}
                                            @php
                                                $isOwner = auth()->id() === $attachment->uploaded_by;
                                                $isSuperAdmin = auth()->user()->isSuperAdmin();
                                                $isCurrentRoleHolder = auth()->user()->role === $cpb->status;
                                                $isRework = $cpb->is_rework;

                                                // User bisa hapus jika:
                                                // 1. Dia SuperAdmin OR
                                                // 2. Dia pengunggah asli DAN (Batch masih di departemennya OR sedang status Rework)
                                                $canDelete = $isSuperAdmin || ($isOwner && ($isCurrentRoleHolder || $isRework));
                                            @endphp

                                            @if($canDelete)
                                                <form action="{{ route('cpb.attachment.destroy', ['cpb' => $cpb->id, 'attachment' => $attachment->id]) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus lampiran ini?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-sm btn-light disabled" title="Terkunci (Batch sudah berpindah)">
                                                    <i class="fas fa-lock text-muted"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-folder-open text-muted fa-3x mb-3 opacity-50"></i>
                        <p class="text-muted mb-0">Belum ada lampiran dokumen untuk batch ini.</p>
                    </div>
                @endif

                @php $isHandler = (auth()->user()->role === $cpb->status || auth()->user()->isSuperAdmin()); @endphp
                @if($isHandler && $cpb->status !== 'released')
                    <div class="mt-4 p-3 bg-light rounded border border-dashed text-center">
                        <button class="btn btn-sm btn-primary px-4 shadow-sm" type="button" data-toggle="collapse" data-target="#uploadSection">
                            <i class="fas fa-upload mr-1"></i> Unggah Dokumen Tahap {{ strtoupper($cpb->status) }}
                        </button>
                        <div class="collapse mt-3" id="uploadSection">
                            <form action="{{ route('cpb.upload', $cpb) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row justify-content-center text-left">
                                    <div class="col-md-8">
                                        <div class="form-group mb-2">
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="file" name="file" required>
                                                <label class="custom-file-label" for="file">Pilih berkas PDF/Gambar...</label>
                                            </div>
                                        </div>
                                        <div class="form-group mb-2">
                                            <input type="text" class="form-control form-control-sm" name="description" placeholder="Deskripsi dokumen (Contoh: Laporan Hasil Produksi)">
                                        </div>
                                        <button type="submit" class="btn btn-dark btn-sm btn-block shadow-sm">
                                            SIMPAN LAMPIRAN
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Log Perjalanan --}}
        <div class="card shadow-sm border-light">
            <div class="card-header bg-white"><h3 class="card-title font-weight-bold">Log Perjalanan Batch</h3></div>
            <div class="card-body">
                <div class="timeline timeline-inverse">
                    @foreach($cpb->handoverLogs->sortByDesc('handed_at') as $log)
                        <div class="time-label">
                            <span class="{{ str_contains(strtoupper($log->notes ?? ''), 'REJECT') ? 'bg-danger' : 'bg-secondary' }} px-3">
                                {{ $log->handed_at->format('d M Y') }}
                            </span>
                        </div>
                        <div>
                            @if(str_contains(strtoupper($log->notes ?? ''), 'REJECT'))
                                <i class="fas fa-undo-alt bg-danger"></i>
                            @else
                                <i class="fas fa-arrow-right bg-success"></i>
                            @endif
                            <div class="timeline-item shadow-none border">
                                <span class="time"><i class="fas fa-clock mr-1"></i> {{ $log->handed_at->format('H:i') }}</span>
                                <h3 class="timeline-header border-0 text-muted">
                                    <strong>{{ $log->sender->name }}</strong> meneruskan dari 
                                    <span class="badge badge-light border">{{ strtoupper($log->from_status) }}</span> ke 
                                    <strong>{{ $log->receiver->name ?? 'Sistem' }}</strong>
                                </h3>
                                @if($log->notes)
                                    <div class="timeline-body bg-light border-left border-info mx-3 my-2 text-sm italic">
                                        "{{ $log->notes }}"
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    <div><i class="fas fa-flag-checkered bg-gray"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        {{-- Tombol Tindakan Utama --}}
        <div class="card card-outline {{ $cpb->time_remaining < 0 ? 'card-danger' : 'card-primary' }} shadow-sm sticky-top" style="top: 1rem;">
            <div class="card-body p-4 text-center">
                <h6 class="text-uppercase text-muted mb-2 letter-spacing-1">Sisa Waktu Proses</h6>
                <div class="d-flex justify-content-center align-items-baseline mb-1">
                    <h1 class="display-4 font-weight-bold mb-0 {{ $cpb->time_remaining < 0 ? 'text-danger' : 'text-primary' }}">
                        {{ $cpb->time_remaining }}
                    </h1>
                    <span class="ml-2 font-weight-bold">JAM</span>
                </div>
                <div class="progress progress-xxs mb-3" style="height: 4px;">
                    @php 
                        $percentage = $cpb->time_limit > 0 ? ($cpb->time_remaining / $cpb->time_limit) * 100 : 0;
                        $percentage = max(0, min(100, $percentage));
                    @endphp
                    <div class="progress-bar {{ $cpb->time_remaining < 0 ? 'bg-danger' : 'bg-primary' }}" style="width: {{ $percentage }}%"></div>
                </div>
                
                <hr>

                <div class="actions">
                    @if(auth()->user()->role === 'qa' || auth()->user()->isSuperAdmin())
                        @if($cpb->is_final_qa)
                            <form action="{{ route('cpb.release', $cpb) }}" method="POST" class="mb-3">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-block btn-lg shadow font-weight-bold py-3" 
                                    onclick="return confirm('Konfirmasi Pelulusan Produk?')">
                                    <i class="fas fa-check-double mr-2"></i> RELEASE PRODUCT
                                </button>
                            </form>
                        @endif
                    @endif

                    @if(auth()->user()->role === $cpb->status || auth()->user()->isSuperAdmin())
                        @can('handover', $cpb)
                            @if(!($cpb->status === 'qa' && $cpb->is_final_qa))
                                @if($hasAttachment)
                                    <a href="{{ route('cpb.handover.form', ['cpb' => $cpb->id]) }}" class="btn btn-success btn-block btn-lg shadow-sm font-weight-bold py-3 mb-3">
                                        <i class="fas fa-paper-plane mr-2"></i> SERAHKAN CPB
                                        <small class="d-block text-xs font-weight-normal mt-1 opacity-75">Kirim ke {{ strtoupper($cpb->getNextDepartment()) }}</small>
                                    </a>
                                @else
                                    <button class="btn btn-secondary btn-block btn-lg py-3 mb-3" onclick="Swal.fire('Dokumen Wajib', 'Unggah laporan/lampiran terlebih dahulu sebelum melakukan handover.', 'warning')">
                                        <i class="fas fa-lock mr-2"></i> SERAHKAN CPB
                                        <small class="d-block text-xs font-weight-normal mt-1">Laporan Belum Diunggah</small>
                                    </button>
                                @endif
                            @endif
                        @endcan
                    @endif

                    @if(auth()->user()->role === $cpb->status || auth()->user()->isSuperAdmin())
                        @if($cpb->getPreviousDepartment() && Gate::allows('handover', $cpb))
                            <button type="button" class="btn btn-outline-danger btn-block py-2" data-toggle="modal" data-target="#modal-reject">
                                <i class="fas fa-undo-alt mr-2"></i> KEMBALIKAN (REWORK)
                            </button>
                        @endif
                    @endif
                    
                    <a href="{{ route('cpb.index') }}" class="btn btn-link btn-block text-muted mt-3">
                        <i class="fas fa-arrow-left mr-1 text-xs"></i> Kembali ke Daftar
                    </a>    
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-reject" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('cpb.reject', $cpb) }}" method="POST">
            @csrf
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold">Instruksi Rework</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body p-4 text-left">
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase font-weight-bold d-block">Dikirim Ke:</label>
                        <div class="h5 font-weight-bold text-danger">{{ strtoupper($cpb->getPreviousDepartment()) }}</div>
                    </div>
                    <div class="form-group">
                        <label>Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="rework_note" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger font-weight-bold">Kirim Rework</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
        
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    });
</script>
@endpush