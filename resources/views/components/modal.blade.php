@props([
    'id',
    'title' => 'Modal Title',
    'size' => 'md', // sm, md, lg, xl
    'footer' => true,
    'static' => false,
])

@php
    $modalSizes = [
        'sm' => 'modal-sm',
        'md' => '',
        'lg' => 'modal-lg',
        'xl' => 'modal-xl',
    ];
    
    $modalSize = $modalSizes[$size] ?? '';
@endphp

<div class="modal fade" id="{{ $id }}" {{ $static ? 'data-backdrop="static" data-keyboard="false"' : '' }}>
    <div class="modal-dialog {{ $modalSize }}">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ $title }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
            @if($footer)
                <div class="modal-footer justify-content-between">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>