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
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi CPB</h3>
                <div class="card-tools">
                    @if($cpb->canBeHandedOverBy(auth()->user()))
                        <a href="{{ route('handover.create', $cpb) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-forward"></i> Serahkan
                        </a>
                    @endif
                    @if($cpb->status == 'rnd' && auth()->user()->id == $cpb->created_by)
                        <a href="{{ route('cpb.edit', $cpb) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                {{-- Alert Rework Global --}}
                @if($cpb->is_rework)
                    <div class="alert alert-warning shadow-sm">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> Status: Rework (Perbaikan)</h5>
                        <p class="mb-0"><strong>Alasan Penolakan:</strong> {{ $cpb->rework_note }}</p>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <dl>
                            <dt>No. Batch:</dt>
                            <dd><strong>{{ $cpb->batch_number }}</strong></dd>
                            <dt>Jenis CPB:</dt>
                            <dd><span class="badge {{ $cpb->type == 'pengolahan' ? 'bg-info' : 'bg-primary' }}">{{ ucfirst($cpb->type) }}</span></dd>
                            <dt>Nama Produk:</dt>
                            <dd>{{ $cpb->product_name }}</dd>
                            <dt>Status Tahap:</dt>
                            <dd>{!! $cpb->status_badge !!}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl>
                            <dt>Dibuat Oleh:</dt>
                            <dd>{{ $cpb->creator->name ?? '-' }}</dd>
                            <dt>Lokasi Saat Ini:</dt>
                            <dd>{{ $cpb->currentDepartment->name ?? '-' }}</dd>
                            <dt>Status Waktu:</dt>
                            <dd>{!! $cpb->time_status_badge !!}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Lampiran Dokumen --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lampiran Dokumen</h3>
            </div>
            <div class="card-body">
                @if($cpb->attachments->count() > 0)
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Nama File</th>
                                <th>Keterangan</th>
                                <th>Oleh</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cpb->attachments as $attachment)
                                <tr>
                                    <td><i class="fas fa-file-alt text-primary mr-1"></i> {{ $attachment->file_name }}</td>
                                    <td>{{ $attachment->description ?? '-' }}</td>
                                    <td>{{ $attachment->uploader->name }}</td>
                                    <td>
                                        <a href="{{ Storage::url($attachment->file_path) }}" class="btn btn-xs btn-default" download><i class="fas fa-download"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted text-center py-3">Belum ada file yang dilampirkan.</p>
                @endif

                <hr>

                {{-- Logic Hak Akses Upload --}}
                @php $isHandler = (auth()->user()->role === $cpb->status || auth()->user()->isSuperAdmin()); @endphp

                @if($isHandler)
                    <form action="{{ route('cpb.upload', $cpb) }}" method="POST" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        <div class="form-group">
                            <label>Unggah File {{ $cpb->is_rework ? 'Hasil Perbaikan (Rework)' : 'Baru' }}</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="file" name="file" required>
                                    <label class="custom-file-label" for="file">Pilih file...</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control form-control-sm" name="description" placeholder="Keterangan file (opsional)">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-upload"></i> Upload</button>
                    </form>
                @else
                    <div class="alert alert-light border mt-2">
                        <i class="fas fa-lock mr-2 text-muted"></i>
                        Hanya departemen <strong>{{ strtoupper($cpb->status) }}</strong> yang dapat menambah dokumen di tahap ini.
                    </div>
                @endif
            </div>
        </div>

        {{-- Riwayat Handover --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Riwayat Handover</h3></div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($handoverLogs as $log)
                        <div class="time-label">
                            <span class="{{ $log->notes && str_contains($log->notes, 'REJECT') ? 'bg-danger' : 'bg-info' }}">
                                {{ $log->handed_at->format('d M Y') }}
                            </span>
                        </div>
                        <div>
                            <i class="fas {{ str_contains($log->notes, 'REJECT') ? 'fa-undo bg-danger' : 'fa-exchange-alt bg-blue' }}"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fas fa-clock"></i> {{ $log->handed_at->format('H:i') }}</span>
                                <h3 class="timeline-header"><strong>{{ $log->sender->name }}</strong> -> <strong>{{ $log->receiver->name ?? 'System' }}</strong></h3>
                                <div class="timeline-body">
                                    <small class="text-muted">Dari: {{ ucfirst($log->from_status) }} | Ke: {{ ucfirst($log->to_status) }}</small>
                                    @if($log->notes)<div class="mt-2 p-2 bg-white border-left border-info">{{ $log->notes }}</div>@endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div><i class="fas fa-clock bg-gray"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        {{-- Status Card --}}
        <div class="card card-outline card-primary shadow">
            <div class="card-body">
                <h5 class="text-center mb-3">Sisa Waktu Di Tahap Ini</h5>
                <div class="text-center">
                    <h2 class="{{ $cpb->time_remaining < 0 ? 'text-danger' : 'text-primary' }} font-weight-bold">
                        {{ $cpb->time_remaining }} <small>jam</small>
                    </h2>
                    <p class="text-muted small">Batas: {{ $cpb->time_limit }} jam</p>
                </div>
                
                <hr>

                {{-- Tombol Tindakan --}}
                <div class="btn-group-vertical w-100">
                    @if($cpb->getPreviousDepartment() && Gate::allows('handover', $cpb))
                        <button type="button" class="btn btn-danger mb-2" data-toggle="modal" data-target="#modal-reject">
                            <i class="fas fa-undo"></i> Kembalikan (Rework)
                        </button>
                    @endif

                    @if($nextDepartment && $isHandler)
                        <a href="{{ route('handover.create', $cpb) }}" class="btn btn-success mb-2">
                            <i class="fas fa-forward"></i> Serahkan ke {{ strtoupper($nextDepartment) }}
                        </a>
                    @endif
                    
                    <a href="{{ route('cpb.index') }}" class="btn btn-default"><i class="fas fa-list"></i> Daftar CPB</a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Reject --}}
<div class="modal fade" id="modal-reject" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('cpb.reject', $cpb) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h4 class="modal-title">Konfirmasi Rework</h4>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Batch akan dikembalikan ke: <strong>{{ strtoupper($cpb->getPreviousDepartment()) }}</strong></p>
                    <div class="form-group">
                        <label>Alasan Penolakan / Detail Perbaikan</label>
                        <textarea name="rework_note" class="form-control" rows="3" required placeholder="Jelaskan apa yang harus diperbaiki..."></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger text-white">Ya, Kembalikan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        // Custom file input label update
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    });
</script>
@endpush