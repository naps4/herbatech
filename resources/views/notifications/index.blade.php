{{-- resources/views/notifications/index.blade.php --}}
@extends('layouts.app')

@section('title')
@section('page-title')

@endsection

@section('content')
<div class="row-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Notifikasi</h3>
                <div class="card-tools">
                    <form action="{{ route('notifications.clear') }}" method="POST" class="d-inline" 
                          onsubmit="return confirm('Hapus semua notifikasi?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Hapus Semua
                        </button>
                    </form>
                    <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline ml-1">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="fas fa-check-double"></i> Tandai Semua Dibaca
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Pesan</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notifications as $notification)
                                <tr class="{{ $notification->is_read ? '' : 'table-info' }}">
                                    <td>{{ $loop->iteration + ($notifications->currentPage() - 1) * $notifications->perPage() }}</td>
                                    <td>
                                        <div class="d-flex">
                                            @if(!$notification->is_read)
                                                <span class="badge bg-warning mr-2">NEW</span>
                                            @endif
                                            <div>
                                                <strong>{{ $notification->message }}</strong>
                                                @if($notification->cpb)
                                                    <br>
                                                    <small class="text-muted">
                                                        CPB: {{ $notification->cpb->batch_number }}
                                                    </small>
                                                @endif
                                                @if($notification->data)
                                                    <br>
                                                    <small class="text-muted">
                                                        @foreach($notification->data as $key => $value)
                                                            {{ $key }}: {{ $value }}@if(!$loop->last), @endif
                                                        @endforeach
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $notification->created_at->format('d/m/Y') }}<br>
                                        <small>{{ $notification->created_at?->format('H:i:s') ?? '' }}</small>
                                    </td>
                                    <td>
                                        @if($notification->is_read)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Dibaca
                                            </span>
                                            <br>
                                            <small>{{ $notification->read_at?->format('d/m/Y H:i') ?? '-' }}</small>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-envelope"></i> Baru
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('notifications.show', $notification) }}" 
                                               class="btn btn-sm btn-info" title="Lihat">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(!$notification->is_read)
                                                <form action="{{ route('notifications.mark-as-read', $notification) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Tandai Dibaca">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Tidak ada notifikasi</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $notifications->links() }}
                <div class="float-right">
                    <small class="text-muted">
                        Total: {{ $notifications->total() }} notifikasi
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // AJAX untuk mark as read
    $('.mark-as-read-btn').click(function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const button = $(this);
        
        $.post(url, function(response) {
            if (response.success) {
                button.closest('tr').removeClass('table-info');
                button.closest('td').find('.badge').removeClass('bg-warning').addClass('bg-success');
                button.closest('td').find('.badge i').removeClass('fa-envelope').addClass('fa-check');
                button.remove();
                
                // Update notification count
                updateNotificationCount();
            }
        });
    });
    
    // AJAX untuk mark all as read
    $('#mark-all-read-btn').click(function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const button = $(this);
        
        $.post(url, function(response) {
            if (response.success) {
                $('tr.table-info').removeClass('table-info');
                $('.badge.bg-warning').removeClass('bg-warning').addClass('bg-success');
                $('.badge i.fa-envelope').removeClass('fa-envelope').addClass('fa-check');
                $('.mark-as-read-btn').remove();
                
                // Update notification count
                updateNotificationCount();
                
                alert('Semua notifikasi telah ditandai sebagai dibaca.');
            }
        });
    });
    
    function updateNotificationCount() {
        $.get('/api/notifications/unread-count', function(data) {
            $('#notification-count').text(data.count);
            if (data.count > 0) {
                $('#notification-count').addClass('badge-danger').removeClass('badge-warning');
            } else {
                $('#notification-count').addClass('badge-warning').removeClass('badge-danger');
            }
        });
    }
});
</script>
@endpush