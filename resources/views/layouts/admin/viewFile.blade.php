@extends('layouts.master')
@section('title', __('archive.preview_title') . ' — ' . ($file->file_name ?? ''))
@section('page-header')
    @include('layouts.partials.page-header', [
        'title' => __('archive.preview_title'),
        'subtitle' => $file->file_name . ' — ' . $file->code,
        'breadcrumbs' => [
            ['label' => __('archive.home'), 'url' => $homeRoute],
            ['label' => __('archive.documents'), 'url' => route('manageFile')],
            ['label' => __('archive.preview_title')],
        ],
    ])
@endsection
@section('content')
    <div class="archive-card">
        <div class="archive-card-header">
            <div>
                <h5 class="mb-1">{{ $file->file_name }}</h5>
                <small class="text-archive-muted">{{ __('archive.code') }}: {{ $file->code }} &middot; {{ __('archive.status') }}: {{ archive_status_label($file->status_id) }}</small>
            </div>
            <div class="quick-actions">
                <a href="{{ route('streamFile', $file) }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-1"></i>{{ __('archive.open_in_tab') }}
                </a>
                <a href="{{ route('manageFile') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-right me-1"></i>{{ __('archive.back') }}
                </a>
            </div>
        </div>
        <div class="archive-card-body p-2">
            <iframe class="file-preview-frame" src="{{ $streamUrl }}" title="{{ __('archive.preview_title') }}: {{ $file->file_name }}"></iframe>
        </div>
    </div>
@endsection
