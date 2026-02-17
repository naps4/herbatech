@extends('layouts.app')

@section('title', 'Daftar CPB')
@section('page-title', 'Daftar CPB')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Daftar CPB</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
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
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua</option>
                                    <option value="rnd" {{ request('status') == 'rnd' ? 'selected' : '' }}>RND</option>
                                    <option value="qa" {{ request('status') == 'qa' ? 'selected' : '' }}>QA</option>
                                    <option value="ppic" {{ request('status') == 'ppic' ? 'selected' : '' }}>PPIC</option>
                                    <option value="wh" {{ request('status') == 'wh' ? 'selected' : '' }}>Warehouse</option>
                                    <option value="produksi" {{ request('status') == 'produksi' ? 'selected' : '' }}>Produksi</option>
                                    <option value="qc" {{ request('status') == 'qc' ? 'selected' : '' }}>QC</option>
                                    <option value="qa_final" {{ request('status') == 'qa_final' ? 'selected' : '' }}>QA Final</option>
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
                                       placeholder="Cari batch number">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('cpb.index') }}" class="btn btn-default">
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
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar CPB</h3>
                <div class="card-tools">
                    @can('create', App\Models\CPB::class)
                    <a href="{{ route('cpb.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> CPB Baru
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No. Batch</th>
                                <th>Jenis</th>
                                <th>Produk</th>
                                <th>Status</th>
                                <th>Lokasi Saat Ini</th>
                                <th>Running Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cpbs as $cpb)
                                <tr>
                                    <td>
                                        <strong>{{ $cpb->batch_number }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge {{ $cpb->type == 'pengolahan' ? 'bg-info' : 'bg-primary' }}">
                                            {{ ucfirst($cpb->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $cpb->product_name }}</td>
                                    <td>{!! $cpb->status_badge !!}</td>
                                    <td>
                                        @if($cpb->currentDepartment)
                                            {{ $cpb->currentDepartment->name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="far fa-clock mr-1"></i> {{ $cpb->formatted_duration }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('cpb.show', $cpb) }}" class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($cpb->status == 'rnd' && auth()->user()->id == $cpb->created_by)
                                                <a href="{{ route('cpb.edit', $cpb) }}" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if($cpb->canBeHandedOverBy(auth()->user()))
                                                <a href="{{ route('handover.create', $cpb) }}" class="btn btn-sm btn-success" title="Handover">
                                                    <i class="fas fa-forward"></i>
                                                </a>
                                            @endif
                                            @if(($cpb->status == 'qa_final' && auth()->user()->role == 'qa') || auth()->user()->isSuperAdmin())
                                                <form action="{{ route('cpb.release', $cpb) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            title="Release" 
                                                            onclick="return confirm('Release CPB ini?')">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data CPB</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $cpbs->links() }}
                <div class="float-right">
                    <small class="text-muted">
                        Total: {{ $cpbs->total() }} CPB
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection