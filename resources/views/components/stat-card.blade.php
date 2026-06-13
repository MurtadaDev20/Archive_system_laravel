@props(['label', 'value', 'icon' => 'bi-graph-up', 'variant' => 'primary', 'footer' => null])

<div class="stat-card">
    <div class="d-flex align-items-start justify-content-between gap-3">
        <div>
            <div class="stat-card-label">{{ $label }}</div>
            <div class="stat-card-value">{{ $value }}</div>
        </div>
        <div class="stat-card-icon {{ $variant }}">
            <i class="bi {{ $icon }}"></i>
        </div>
    </div>
    @if($footer)
        <div class="stat-card-footer">{{ $footer }}</div>
    @endif
</div>
