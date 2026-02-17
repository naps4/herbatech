@props(['notification'])

<div class="dropdown-item">
    <div class="d-flex">
        @if(!$notification->is_read)
            <span class="badge bg-warning mr-2">NEW</span>
        @endif
        <div class="flex-grow-1">
            <div class="font-weight-bold">{{ $notification->message }}</div>
            @if($notification->cpb)
                <div class="text-muted small">
                    CPB: {{ $notification->cpb->batch_number }}
                </div>
            @endif
        </div>
        <div class="text-right">
            <div class="text-muted small">
                {{ $notification->created_at->diffForHumans() }}
            </div>
            @if($notification->is_read)
                <div class="text-success small">
                    <i class="fas fa-check"></i> Dibaca
                </div>
            @endif
        </div>
    </div>
</div>