@props(['type' => 'info', 'dismissible' => true])

@php
    $alertClasses = [
        'success' => 'alert-success',
        'danger' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
    ];
    
    $iconClasses = [
        'success' => 'fa-check',
        'danger' => 'fa-ban',
        'warning' => 'fa-exclamation-triangle',
        'info' => 'fa-info-circle',
    ];
    
    $alertClass = $alertClasses[$type] ?? 'alert-info';
    $iconClass = $iconClasses[$type] ?? 'fa-info-circle';
@endphp

<div class="alert {{ $alertClass }} {{ $dismissible ? 'alert-dismissible' : '' }}">
    @if($dismissible)
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    @endif
    <h5><i class="icon fas {{ $iconClass }}"></i> {{ ucfirst($type) }}!</h5>
    {{ $slot }}
</div>