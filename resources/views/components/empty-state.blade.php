@props(['icon' => 'bi-inbox', 'title' => 'No data found', 'message' => 'There is nothing to display yet.', 'actionLabel' => null, 'actionUrl' => null])

<div class="empty-state">
    <div class="empty-state-icon">
        <i class="bi {{ $icon }}"></i>
    </div>
    <h6>{{ $title }}</h6>
    <p>{{ $message }}</p>
    @if($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-archive-accent btn-sm">
            <i class="bi bi-plus-lg me-1"></i>{{ $actionLabel }}
        </a>
    @endif
</div>
