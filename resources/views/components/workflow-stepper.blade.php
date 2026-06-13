@props(['document', 'workflow'])

@php
    $currentSlug = $workflow->normalizeSlug($document->status?->slug ?? 'draft');
    $pipeline = $workflow->pipelineSlugs();
    $currentIndex = array_search($currentSlug, $pipeline, true);
    $rawSlug = $document->status?->slug ?? 'draft';
@endphp

<div class="workflow-stepper mb-4">
    <div class="workflow-steps d-flex flex-wrap align-items-center gap-1">
        @foreach($pipeline as $index => $slug)
            @php
                $statusId = \App\Models\Status::idForSlug($slug);
                $isDone = $currentIndex !== false && $index < $currentIndex;
                $isCurrent = $slug === $currentSlug;
                $isRejected = $rawSlug === 'rejected';
            @endphp
            <div class="workflow-step {{ $isCurrent ? 'is-current' : '' }} {{ $isDone ? 'is-done' : '' }} {{ $isRejected ? 'is-muted' : '' }}">
                <span class="workflow-step-dot"></span>
                <span class="workflow-step-label">{{ archive_status_label($statusId) }}</span>
            </div>
            @if(!$loop->last)
                <span class="workflow-step-arrow"><i class="bi bi-chevron-left"></i></span>
            @endif
        @endforeach
    </div>

    @if($rawSlug === 'rejected')
        <div class="alert alert-danger border-0 small mt-3 mb-0">
            <i class="bi bi-x-octagon me-1"></i>{{ __('archive.workflow_guidance_rejected') }}
        </div>
    @elseif($rawSlug === 'expired')
        <div class="alert alert-warning border-0 small mt-3 mb-0">
            <i class="bi bi-clock-history me-1"></i>{{ __('archive.workflow_guidance_expired') }}
        </div>
    @else
        <div class="alert alert-light border small mt-3 mb-0">
            <i class="bi bi-lightbulb me-1"></i>{{ $workflow->guidanceFor($document) }}
            @if($next = $workflow->nextStepLabel($currentSlug))
                <span class="text-archive-muted"> — {{ __('archive.workflow_next_step') }}: <strong>{{ $next }}</strong></span>
            @endif
        </div>
    @endif
</div>
