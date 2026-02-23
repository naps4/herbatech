@extends('layouts.app')

@section('title', 'Laporan Riwayat CPB')
@section('page-title', 'Laporan Riwayat CPB')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold">Filter Laporan</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.index') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="start_date">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" 
                                       class="form-control" value="{{ request('start_date') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="end_date">Tanggal Akhir</label>
                                <input type="date" name="end_date" id="end_date" 
                                       class="form-control" value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="type">Jenis CPB</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>Semua</option>
                                    <option value="pengolahan" {{ request('type') == 'pengolahan' ? 'selected' : '' }}>Pengolahan</option>
                                    <option value="pengemasan" {{ request('type') == 'pengemasan' ? 'selected' : '' }}>Pengemasan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif (Belum Release)</option>
                                    <option value="released" {{ request('status') == 'released' ? 'selected' : '' }}>Released (Selesai)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="overdue">Overdue</label>
                                <select name="overdue" id="overdue" class="form-control">
                                    <option value="all" {{ request('overdue') == 'all' ? 'selected' : '' }}>Semua</option>
                                    <option value="yes" {{ request('overdue') == 'yes' ? 'selected' : '' }}>Ya</option>
                                    <option value="no" {{ request('overdue') == 'no' ? 'selected' : '' }}>Tidak</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary shadow-sm">
                                <i class="fas fa-filter"></i> Terapkan Filter
                            </button>
                            @if(auth()->user()->isQA() || auth()->user()->isSuperAdmin())
                                <a href="{{ route('reports.export', request()->query()) }}" class="btn btn-success shadow-sm">
                                    <i class="fas fa-file-excel"></i> Export Excel
                                </a>
                            @endif
                            <a href="{{ route('reports.index') }}" class="btn btn-default border">
                                <i class="fas fa-sync-alt"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Statistics Widget --}}
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-none border">
            <span class="info-box-icon bg-info elevation-0"><i class="fas fa-clipboard-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total CPB Diakses</span>
                <span class="info-box-number h4 mb-0">{{ $total }}</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-none border">
            <span class="info-box-icon bg-success elevation-0"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Status Released</span>
                <span class="info-box-number h4 mb-0">{{ $released }}</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-none border">
            <span class="info-box-icon bg-danger elevation-0"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Status Overdue</span>
                <span class="info-box-number h4 mb-0">{{ $overdue }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-none border">
            <span class="info-box-icon bg-secondary elevation-0"><i class="fas fa-history"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-xs">Akses Role</span>
                <span class="info-box-number text-sm">{{ strtoupper(auth()->user()->role) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h3 class="card-title font-weight-bold">Hasil Filter Laporan</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-valign-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="pl-4">No. Batch</th>
                                <th>Jenis</th>
                                <th>Produk</th>
                                <th>Tahapan / Lokasi</th>
                                <th>Pemegang Saat Ini</th>
                                <th class="text-center">Status Waktu</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cpbs as $cpb)
                                <tr>
                                    <td class="pl-4 font-weight-bold text-primary">{{ $cpb->batch_number }}</td>
                                    <td>
                                        <span class="badge {{ $cpb->type == 'pengolahan' ? 'badge-info' : 'badge-primary' }} text-uppercase">
                                            {{ $cpb->type }}
                                        </span>
                                    </td>
                                    <td>{{ $cpb->product_name }}</td>
                                    <td>
                                        {!! $cpb->status_badge !!}
                                        @if($cpb->status !== auth()->user()->role && $cpb->status !== 'released')
                                            <div class="mt-1">
                                                <span class="badge badge-light border text-muted" style="font-size: 10px;">
                                                    <i class="fas fa-history mr-1"></i> Pernah Dilewati
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <i class="fas fa-user-circle text-muted mr-1"></i>
                                        {{ $cpb->currentDepartment->name ?? 'Sistem / Selesai' }}
                                    </td>
                                    <td class="text-center">
                                        {!! $cpb->time_status_badge !!}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('cpb.show', $cpb) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail Log">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                        <p>Data laporan tidak ditemukan.</p>
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
                            Menampilkan <strong>{{ $cpbs->firstItem() ?? 0 }}</strong> - <strong>{{ $cpbs->lastItem() ?? 0 }}</strong> dari <strong>{{ $cpbs->total() }}</strong> CPB
                        </p>
                    </div>
                    <div>
                        {{ $cpbs->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table td, .table th { vertical-align: middle !important; }
    .info-box { min-height: 80px; }
    .badge-light.border { border: 1px solid #dee2e6 !important; background-color: #f8f9fa; }
</style>
@endpush