@extends('layouts.app')

@section('title', 'Audit Trail')
@section('page-title', 'Audit Trail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Laporan</a></li>
    <li class="breadcrumb-item active">Audit Trail</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter Audit Trail</h3>
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="user_id">User</label>
                                <select name="user_id" id="user_id" class="form-control">
                                    <option value="">Semua User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->role }})
                                        </option>
                                    @endforeach
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
                            <a href="{{ route('reports.audit') }}" class="btn btn-default">
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
                <h3 class="card-title">Riwayat Handover</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>CPB</th>
                                <th>Dari</th>
                                <th>Ke</th>
                                <th>Diserahkan Oleh</th>
                                <th>Diterima Oleh</th>
                                <th>Durasi (jam)</th>
                                <th>Status</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($handovers as $handover)
                                <tr class="{{ $handover->was_overdue ? 'table-danger' : '' }}">
                                    <td>
                                        {{ $handover->handed_at->format('d/m/Y') }}<br>
                                        <small>{{ $handover->handed_at->format('H:i:s') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $handover->cpb->batch_number }}</strong><br>
                                        <small>{{ $handover->cpb->product_name }}</small>
                                    </td>
                                    <td>{{ ucfirst($handover->from_status) }}</td>
                                    <td>{{ ucfirst($handover->to_status) }}</td>
                                    <td>{{ $handover->sender->name }}</td>
                                    <td>{{ $handover->receiver->name ?? '-' }}</td>
                                    <td class="text-right">{{ $handover->duration_in_hours ?? '-' }}</td>
                                    <td>
                                        @if($handover->was_overdue)
                                            <span class="badge bg-danger">Overdue</span>
                                        @else
                                            <span class="badge bg-success">On Time</span>
                                        @endif
                                    </td>
                                    <td>{{ $handover->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $handovers->links() }}
                <div class="float-right">
                    <small class="text-muted">
                        Menampilkan {{ $handovers->count() }} dari {{ $handovers->total() }} handover
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection