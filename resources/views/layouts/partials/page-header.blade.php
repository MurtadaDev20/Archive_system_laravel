@props(['title', 'subtitle' => null, 'breadcrumbs' => []])

<div class="page-header-block d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
    <div>
        <h1>{{ $title }}</h1>
        @if($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @if(count($breadcrumbs))
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb archive-breadcrumb mb-0">
                @foreach($breadcrumbs as $crumb)
                    @if($loop->last)
                        <li class="breadcrumb-item active" aria-current="page">{{ $crumb['label'] }}</li>
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ $crumb['url'] ?? '#' }}">{{ $crumb['label'] }}</a>
                        </li>
                    @endif
                @endforeach
            </ol>
        </nav>
    @endif
</div>
