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
                    @if($cpb->status == 'qa' && auth()->user()->role == 'ppic')
                        <form action="{{ route('cpb.request', $cpb) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning">
                                <i class="fas fa-bullhorn"></i> Request CPB
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl>
                            <dt>No. Batch:</dt>
                            <dd><strong>{{ $cpb->batch_number }}</strong></dd>
                            
                            <dt>Jenis CPB:</dt>
                            <dd>
                                <span class="badge {{ $cpb->type == 'pengolahan' ? 'bg-info' : 'bg-primary' }}">
                                    {{ ucfirst($cpb->type) }}
                                </span>
                            </dd>
                            
                            <dt>Nama Produk:</dt>
                            <dd>{{ $cpb->product_name }}</dd>
                            
                            <dt>Status:</dt>
                            <dd>{!! $cpb->status_badge !!}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl>
                            <dt>Durasi Produksi:</dt>
                            <dd>{{ $cpb->schedule_duration }} jam</dd>
                            
                            <dt>Dibuat Oleh:</dt>
                            <dd>{{ $cpb->creator->name ?? '-' }}</dd>
                            
                            <dt>Lokasi Saat Ini:</dt>
                            <dd>{{ $cpb->currentDepartment->name ?? '-' }}</dd>
                            
                            <dt>Status Waktu:</dt>
                            <dd>{!! $cpb->time_status_badge !!}</dd>
                        </dl>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="progress-group">
                            <span class="progress-text">Progress CPB</span>
                            <span class="float-right"><b>{{ $cpb->progress_percentage }}%</b></span>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-primary" style="width: {{ $cpb->progress_percentage }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Attachments Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lampiran Dokumen</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($cpb->attachments->count() > 0)
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Nama File</th>
                                <th>Keterangan</th>
                                <th>Diupload Oleh</th>
                                <th>Waktu</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cpb->attachments as $attachment)
                                <tr>
                                    <td>
                                        <a href="{{ Storage::url($attachment->file_path) }}" target="_blank">
                                            <i class="fas fa-file mr-1"></i> {{ $attachment->file_name }}
                                        </a>
                                    </td>
                                    <td>{{ $attachment->description ?? '-' }}</td>
                                    <td>{{ $attachment->uploader->name }}</td>
                                    <td>{{ $attachment->created_at->format('d/m/y H:i') }}</td>
                                    <td>
                                        <a href="{{ Storage::url($attachment->file_path) }}" class="btn btn-xs btn-default" download>
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted text-center my-3">Belum ada file yang dilampirkan.</p>
                @endif
                
                <hr>
                
                <form action="{{ route('cpb.upload', $cpb) }}" method="POST" enctype="multipart/form-data" class="mt-3">
                    @csrf
                    <div class="form-group">
                        <label for="file">Upload File Baru</label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="file" name="file" required>
                                <label class="custom-file-label" for="file">Pilih file...</label>
                            </div>
                        </div>
                        <small class="text-muted">Max: 10MB. Format: PDF, Image, Doc, dll.</small>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="description" placeholder="Keterangan file (opsional)">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </form>
            </div>
        </div>

        <!-- Handover History -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Riwayat Handover</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($handoverLogs as $log)
                        <div class="time-label">
                            <span class="bg-{{ $log->was_overdue ? 'danger' : 'info' }}">
                                {{ $log->handed_at->format('d M Y') }}
                            </span>
                        </div>
                        
                        <div>
                            <i class="fas fa-exchange-alt bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time">
                                    <i class="fas fa-clock"></i> {{ $log->handed_at->format('H:i') }}
                                    @if($log->received_at)
                                        - {{ $log->received_at->format('H:i') }}
                                    @endif
                                </span>
                                <h3 class="timeline-header">
                                    <strong>{{ $log->sender->name }}</strong> 
                                    menyerahkan ke 
                                    <strong>{{ $log->receiver->name ?? 'Belum diterima' }}</strong>
                                </h3>
                                <div class="timeline-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Dari:</strong> {{ ucfirst($log->from_status) }}<br>
                                            <strong>Ke:</strong> {{ ucfirst($log->to_status) }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Durasi:</strong> {{ $log->duration_formatted }}<br>
                                            @if($log->was_overdue)
                                                <span class="badge bg-danger">Overdue</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($log->notes)
                                        <hr class="my-2">
                                        <strong>Catatan:</strong> {{ $log->notes }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Status Card -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Status Saat Ini</h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    {!! $cpb->status_badge !!}
                </div>
                
                <dl>
                    <dt>Mulai Status Ini:</dt>
                    <dd>{{ $cpb->entered_current_status_at->format('d/m/Y H:i') }}</dd>
                    
                    <dt>Durasi:</dt>
                    <dd>{{ $cpb->duration_in_current_status }} jam</dd>
                    
                    <dt>Batas Waktu:</dt>
                    <dd>{{ $cpb->time_limit }} jam</dd>
                    
                    <dt>Sisa Waktu:</dt>
                    <dd>
                        <span class="{{ $cpb->time_remaining < 0 ? 'text-danger' : '' }}">
                            {{ $cpb->time_remaining }} jam
                        </span>
                    </dd>
                </dl>
                
                @if($cpb->is_overdue)
                    <div class="alert alert-danger">
                        <h6><i class="icon fas fa-exclamation-triangle"></i> OVERDUE!</h6>
                        <p class="mb-0">
                            CPB telah melebihi batas waktu sejak 
                            {{ $cpb->overdue_since->format('d/m/Y H:i') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Next Action Card -->
        @if($nextDepartment && $canHandover)
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Tindakan Selanjutnya</h3>
                </div>
                <div class="card-body">
                    <p>Anda dapat menyerahkan CPB ini ke departemen berikutnya:</p>
                    
                    <div class="text-center mb-3">
                        <div class="display-4">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        <h4>{{ strtoupper($nextDepartment) }}</h4>
                    </div>
                    
                    <a href="{{ route('handover.create', $cpb) }}" class="btn btn-success btn-block">
                        <i class="fas fa-forward"></i> Serahkan ke {{ ucfirst($nextDepartment) }}
                    </a>
                </div>
            </div>
        @endif
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="btn-group-vertical w-100">
                    <a href="{{ route('cpb.index') }}" class="btn btn-default">
                        <i class="fas fa-list"></i> Kembali ke Daftar
                    </a>
                    @if($cpb->status == 'qa_final' && auth()->user()->role == 'qa')
                        <form action="{{ route('cpb.release', $cpb) }}" method="POST" class="w-100">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100 text-left" 
                                    onclick="return confirm('Release CPB ini?')">
                                <i class="fas fa-check-circle"></i> Release CPB
                            </button>
                        </form>
                    @endif
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isQA())
                        <a href="{{ route('reports.audit', ['batch_number' => $cpb->batch_number]) }}" 
                           class="btn btn-info">
                            <i class="fas fa-history"></i> Lihat Audit Trail
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding: 0;
    list-style: none;
}
.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #dee2e6;
    left: 31px;
    margin: 0;
    border-radius: 2px;
}
.timeline > li {
    position: relative;
    margin-bottom: 30px;
    min-height: 50px;
}
.timeline > li:after {
    content: "";
    display: table;
    clear: both;
}
.time-label {
    position: relative;
    display: block;
    padding: 10px;
    background: #e9ecef;
    border-radius: 4px;
    margin: 0 0 30px 0;
}
.time-label > span {
    display: inline-block;
    padding: 5px 10px;
    color: white;
    border-radius: 4px;
}
.timeline-item {
    position: relative;
    margin-left: 60px;
    margin-right: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}
.timeline-header {
    margin: 0 0 10px 0;
    font-size: 16px;
    line-height: 1.1;
}
.timeline-body {
    padding: 10px;
}
.timeline-time {
    color: #999;
    font-size: 12px;
}
.timeline > li > .fa {
    position: absolute;
    left: 23px;
    top: 0;
    width: 16px;
    height: 16px;
    color: #fff;
    background: #6c757d;
    border-radius: 50%;
    text-align: center;
    line-height: 16px;
    font-size: 10px;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh every 2 minutes
    setInterval(function() {
        window.location.reload();
    }, 120000);
});
</script>
@endpush