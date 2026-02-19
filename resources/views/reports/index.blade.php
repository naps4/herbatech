@extends('layouts.app')

@section('title')
@section('page-title')
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter Laporan</h3>
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
                                    <option value="released" {{ request('status') == 'released' ? 'selected' : '' }}>Released</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
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
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('reports.export', request()->all()) }}" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                            <a href="{{ route('reports.index') }}" class="btn btn-default">
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
    <!-- Statistics -->
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-clipboard-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total CPB</span>
                <span class="info-box-number">{{ $total }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
                <span class="progress-description">
                    Total semua CPB
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Released</span>
                <span class="info-box-number">{{ $released }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ $total > 0 ? ($released / $total) * 100 : 0 }}%"></div>
                </div>
                <span class="progress-description">
                    {{ $total > 0 ? round(($released / $total) * 100, 1) : 0 }}% dari total
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Overdue</span>
                <span class="info-box-number">{{ $overdue }}</span>
                <div class="progress">
                    <div class="progress-bar" style="width: {{ $total > 0 ? ($overdue / $total) * 100 : 0 }}%"></div>
                </div>
                <span class="progress-description">
                    {{ $total > 0 ? round(($overdue / $total) * 100, 1) : 0 }}% dari total
                </span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Avg Duration</span>
                <span class="info-box-number">{{ round($averageDuration, 1) }} jam</span>
                <div class="progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
                <span class="progress-description">
                    Rata-rata durasi per tahap
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar CPB </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No. Batch</th>
                                <th>Jenis</th>
                                <th>Produk</th>
                                <th>Status</th>
                                <th>Dibuat Oleh</th>
                                <th>Lokasi Saat Ini</th>
                                <th>Durasi (jam)</th>
                                <th>Status Waktu</th>
                                <th>Tanggal Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cpbs as $cpb)
                                <tr class="{{ $cpb->is_overdue ? 'table-danger' : '' }}">
                                    <td>{{ $cpb->batch_number }}</td>
                                    <td>{{ ucfirst($cpb->type) }}</td>
                                    <td>{{ $cpb->product_name }}</td>
                                    <td>{!! $cpb->status_badge !!}</td>
                                    <td>{{ $cpb->creator->name ?? '-' }}</td>
                                    <td>{{ $cpb->currentDepartment->name ?? '-' }}</td>
                                    <td class="text-right">{{ $cpb->duration_in_current_status }}</td>
                                    <td>{!! $cpb->time_status_badge !!}</td>
                                    <td>{{ $cpb->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $cpbs->links() }}
                <div class="float-right">
                    <small class="text-muted">
                        Menampilkan {{ $cpbs->count() }} dari {{ $cpbs->total() }} CPB
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection