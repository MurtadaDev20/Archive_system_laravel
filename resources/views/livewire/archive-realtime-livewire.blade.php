@php($pollSeconds = (int) config('archive.realtime_poll_seconds', 0))
<div
    @if($pollSeconds > 0) wire:poll.{{ $pollSeconds }}s="sync" @endif
    class="d-none"
    aria-hidden="true"
    data-archive-realtime-poll="{{ $pollSeconds }}"
></div>
