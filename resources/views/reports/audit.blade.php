@extends('layouts.app')

@section('title', 'Audit Trail Serah Terima')
@section('page-title', 'Audit Trail Serah Terima')

@section('content')
<div class="row">
    <div class="col-12">
        {{-- Card Filter --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold"><i class="fas fa-filter mr-1 text-primary"></i> Filter Audit</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.audit') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tanggal Mulai</label>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tanggal Akhir</label>
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>No. Batch</label>
                                <input type="text" name="batch_number" class="form-control" placeholder="Cari nomor batch..." value="{{ request('batch_number') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="d-none d-md-block">&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block shadow-sm">
                                <i class="fas fa-search mr-1"></i> Cari
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel Audit --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h3 class="card-title font-weight-bold">Log Riwayat Serah Terima</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="pl-4">Waktu Kejadian</th>
                                <th>No. Batch</th>
                                <th>Alur Departemen</th>
                                <th>Pengirim (Oleh)</th>
                                <th>Penerima (Kepada)</th>
                                <th>Catatan / Keterangan</th>
                                <th class="text-center pr-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($handovers as $log)
                                <tr>
                                    <td class="pl-4 align-middle">
                                        <div class="font-weight-bold text-dark">{{ $log->handed_at->format('d M Y') }}</div>
                                        <small class="text-muted"><i class="far fa-clock mr-1"></i> {{ $log->handed_at->format('H:i') }} WIB</small>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-info shadow-xs px-2 py-1">{{ $log->cpb->batch_number }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-light border text-uppercase">{{ $log->from_status }}</span>
                                            <i class="fas fa-long-arrow-alt-right mx-2 text-muted"></i>
                                            <span class="badge badge-light border text-uppercase">{{ $log->to_status }}</span>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="text-sm font-weight-bold">{{ $log->sender->name }}</div>
                                        <small class="text-muted text-xs text-uppercase">{{ $log->sender->role }}</small>
                                    </td>
                                    <td class="align-middle">
                                        <div class="text-sm font-weight-bold">{{ $log->receiver->name ?? 'Sistem' }}</div>
                                        <small class="text-muted text-xs text-uppercase">{{ $log->receiver->role ?? '-' }}</small>
                                    </td>
                                    <td class="align-middle">
                                        @if($log->notes)
                                            <span class="text-sm italic text-muted" title="{{ $log->notes }}">
                                                {{ Str::limit($log->notes, 40) }}
                                            </span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center pr-4 align-middle">
                                        <a href="{{ route('cpb.show', $log->cpb_id) }}" class="btn btn-sm btn-outline-primary btn-round" title="Lihat CPB">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-history fa-3x mb-3 opacity-25"></i>
                                        <p>Belum ada riwayat aktivitas serah terima.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-0">
                            Menampilkan <strong>{{ $handovers->firstItem() ?? 0 }}</strong> - <strong>{{ $handovers->lastItem() ?? 0 }}</strong> dari <strong>{{ $handovers->total() }}</strong> log
                        </p>
                    </div>
                    <div>
                        {{ $handovers->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table td, .table th { vertical-align: middle !important; border-top: 1px solid #f4f6f9; }
    .badge-light.border { border: 1px solid #dee2e6 !important; background-color: #f8f9fa; font-weight: 500; }
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .italic { font-style: italic; }
    .btn-round { border-radius: 20px; padding-left: 12px; padding-right: 12px; }
    .opacity-25 { opacity: 0.25; }
    .text-xs { font-size: 0.75rem; }
</style>
@endpush