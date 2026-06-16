@props(['status' => 'pending', 'size' => 'sm'])

@php
    $map = [
        'pending' => ['class' => 'text-bg-secondary', 'icon' => 'bi-hourglass'],
        'processing' => ['class' => 'text-bg-info', 'icon' => 'bi-cpu'],
        'completed' => ['class' => 'text-bg-success', 'icon' => 'bi-check-circle'],
        'failed' => ['class' => 'text-bg-danger', 'icon' => 'bi-exclamation-triangle'],
        'skipped' => ['class' => 'text-bg-light border', 'icon' => 'bi-dash-circle'],
    ];
    $meta = $map[$status] ?? $map['pending'];
@endphp

<span {{ $attributes->merge(['class' => 'badge rounded-pill '.$meta['class'].' badge-ocr-'.$status]) }}>
    <i class="bi {{ $meta['icon'] }} me-1"></i>{{ __('archive.ocr_status_'.$status) }}
</span>
