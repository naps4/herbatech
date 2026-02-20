@extends('layouts.app')

@section('page-title')
    {{-- Bagian ini dikosongkan agar tidak muncul double header di atas card --}}
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        {{-- Card Filter --}}
        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="card-title">Filter CPB</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('cpb.index') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="type">Jenis CPB</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>Semua</option>
                                    <option value="pengolahan" {{ request('type') == 'pengolahan' ? 'selected' : '' }}>Pengolahan</option>
                                    <option value="pengemasan" {{ request('type') == 'pengemasan' ? 'selected' : '' }}>Pengemasan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Status Tahapan</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua</option>
                                    <option value="rnd" {{ request('status') == 'rnd' ? 'selected' : '' }}>RND</option>
                                    <option value="qa" {{ request('status') == 'qa' ? 'selected' : '' }}>QA</option>
                                    <option value="ppic" {{ request('status') == 'ppic' ? 'selected' : '' }}>PPIC</option>
                                    <option value="wh" {{ request('status') == 'wh' ? 'selected' : '' }}>Warehouse</option>
                                    <option value="produksi" {{ request('status') == 'produksi' ? 'selected' : '' }}>Produksi</option>
                                    <option value="qc" {{ request('status') == 'qc' ? 'selected' : '' }}>QC</option>
                                    <option value="released" {{ request('status') == 'released' ? 'selected' : '' }}>Released</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="overdue">Status Waktu</label>
                                <select name="overdue" id="overdue" class="form-control">
                                    <option value="all" {{ request('overdue') == 'all' ? 'selected' : '' }}>Semua</option>
                                    <option value="true" {{ request('overdue') == 'true' ? 'selected' : '' }}>Overdue</option>
                                    <option value="false" {{ request('overdue') == 'false' ? 'selected' : '' }}>On Time</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="batch_number">No. Batch</label>
                                <input type="text" name="batch_number" id="batch_number" 
                                       class="form-control" value="{{ request('batch_number') }}" 
                                       placeholder="Cari nomor batch...">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary shadow-sm px-4">
                                <i class="fas fa-filter mr-1"></i> Terapkan Filter
                            </button>
                            <a href="{{ route('cpb.index') }}" class="btn btn-default">
                                <i class="fas fa-sync-alt mr-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold text-dark">List Data Batch</h3>
                <div class="card-tools">
                    @can('create', App\Models\CPB::class)
                    <a href="{{ route('cpb.create') }}" class="btn btn-sm btn-primary shadow-sm px-3">
                        <i class="fas fa-plus mr-1"></i> Tambah CPB
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="pl-4">No. Batch</th>
                                <th>Jenis</th>
                                <th>Produk</th>
                                <th>Status Tahap</th>
                                <th>Pemegang Saat Ini</th>
                                <th>Status Waktu</th>
                                <th class="text-right pr-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cpbs as $cpb)
                                <tr>
                                    <td class="pl-4 align-middle">
                                        <span class="font-weight-bold text-primary">{{ $cpb->batch_number }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge {{ $cpb->type == 'pengolahan' ? 'bg-info' : 'bg-primary' }} px-2 py-1">
                                            {{ ucfirst($cpb->type) }}
                                        </span>
                                    </td>
                                    <td class="align-middle">{{ $cpb->product_name }}</td>
                                    <td class="align-middle">{!! $cpb->status_badge !!}</td>
                                    <td class="align-middle text-sm text-dark">
                                        <i class="fas fa-user-circle text-muted mr-1"></i>
                                        {{ $cpb->currentDepartment->name ?? '-' }}
                                    </td>
                                    <td class="align-middle">
                                        @if($cpb->is_overdue)
                                            <span class="badge badge-danger shadow-xs">
                                                <i class="fas fa-clock mr-1"></i> OVERDUE
                                            </span>
                                        @else
                                            <span class="badge badge-success shadow-xs">ON TIME</span>
                                        @endif
                                    </td>
                                    <td class="text-right pr-4 align-middle">
                                        <div class="btn-group">
                                            {{-- 1. Detail --}}
                                            <a href="{{ route('cpb.show', $cpb) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            {{-- 2. Edit (Hanya RND di awal) --}}
                                            @if($cpb->status == 'rnd' && auth()->id() == $cpb->created_by)
                                                <a href="{{ route('cpb.edit', $cpb) }}" class="btn btn-sm btn-outline-warning" title="Edit Data">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif

                                            {{-- 3. Handover --}}
                                            @can('handover', $cpb)
                                                @if(!($cpb->status === 'qa' && $cpb->is_final_qa))
                                                <a href="{{ route('handover.create', $cpb) }}" class="btn btn-sm btn-outline-success" title="Serah Terima CPB">
                                                    <i class="fas fa-forward"></i>
                                                </a>
                                                @endif
                                            @endcan

                                            {{-- 4. Release --}}
                                            @if($cpb->status === 'qa' && $cpb->is_final_qa)
                                                @can('release', $cpb)
                                                    <form action="{{ route('cpb.release', $cpb) }}" method="POST" class="d-inline ms-1">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary" 
                                                                title="Luluskan Produk (Release)" 
                                                                onclick="return confirm('Konfirmasi Release Batch {{ $cpb->batch_number }}?')">
                                                            <i class="fas fa-check-double"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 opacity-20"></i>
                                        <p>Tidak ada data CPB yang ditemukan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- Footer Pagination --}}
            <div class="card-footer bg-white border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-0">
                            Menampilkan <strong>{{ $cpbs->firstItem() ?? 0 }}</strong> - <strong>{{ $cpbs->lastItem() ?? 0 }}</strong> dari <strong>{{ $cpbs->total() }}</strong> data
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
    .pagination { margin-bottom: 0; }
    .page-item.active .page-link { background-color: #007bff; border-color: #007bff; }
    .table td, .table th { vertical-align: middle; white-space: nowrap; }
    .opacity-20 { opacity: 0.2; }
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.075); }
</style>
@endpush