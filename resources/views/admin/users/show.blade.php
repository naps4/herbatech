@extends('layouts.admin')

@section('admin-title', 'Detail User: ' . $user->name)
@section('admin-breadcrumb')
    <li class="breadcrumb-item active">Detail User</li>
@endsection

@section('admin-content')
<div class="row">
    <div class="col-md-4">
        <!-- User Info Card -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Informasi User</h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="user-profile-image" style="font-size: 48px; color: #007bff;">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h4>{{ $user->name }}</h4>
                    {!! $user->role_badge !!}
                </div>
                
                <dl>
                    <dt>Email</dt>
                    <dd>{{ $user->email }}</dd>
                    
                    <dt>Departemen</dt>
                    <dd>{{ $user->department }}</dd>
                    
                    <dt>Tanggal Daftar</dt>
                    <dd>{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                    
                    <dt>Terakhir Login</dt>
                    <dd>{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Belum pernah login' }}</dd>
                </dl>
                
                <div class="mt-3">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-block">
                        <i class="fas fa-edit"></i> Edit User
                    </a>
                    @if(auth()->id() != $user->id)
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" 
                          onsubmit="return confirm('Hapus user ini?')" class="mt-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Hapus User
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- User Activity Stats -->
        <div class="row">
            <div class="col-md-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $user->cpbsCreated->count() }}</h3>
                        <p>CPB Dibuat</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $user->handoversGiven->count() }}</h3>
                        <p>Handover Diberikan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $user->handoversReceived->count() }}</h3>
                        <p>Handover Diterima</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $user->cpbsCurrent->count() }}</h3>
                        <p>CPB di Departemen</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent CPBs Created -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">CPB yang Dibuat</h3>
            </div>
            <div class="card-body">
                @if($user->cpbsCreated->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>No. Batch</th>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->cpbsCreated->take(5) as $cpb)
                            <tr>
                                <td>
                                    <a href="{{ route('cpb.show', $cpb) }}">{{ $cpb->batch_number }}</a>
                                </td>
                                <td>{{ ucfirst($cpb->type) }}</td>
                                <td>{!! $cpb->status_badge !!}</td>
                                <td>{{ $cpb->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted">User belum membuat CPB.</p>
                @endif
            </div>
        </div>
        
        <!-- Recent Handovers -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Handover Terakhir</h3>
            </div>
            <div class="card-body">
                @if($user->handoversGiven->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>CPB</th>
                                <th>Dari</th>
                                <th>Ke</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->handoversGiven->take(5) as $handover)
                            <tr>
                                <td>
                                    <a href="{{ route('cpb.show', $handover->cpb) }}">
                                        {{ $handover->cpb->batch_number }}
                                    </a>
                                </td>
                                <td>{{ strtoupper($handover->from_status) }}</td>
                                <td>{{ strtoupper($handover->to_status) }}</td>
                                <td>{{ $handover->handed_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted">User belum melakukan handover.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection