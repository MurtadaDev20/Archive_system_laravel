@extends('layouts.master')
@section('title', __('archive.document_details') . ' — ' . __('archive.app_name'))
@section('page-header')
    @include('layouts.partials.page-header', [
        'title' => __('archive.document_details'),
        'subtitle' => __('archive.document_details_subtitle'),
        'breadcrumbs' => [
            ['label' => __('archive.home'), 'url' => $homeRoute],
            ['label' => __('archive.documents'), 'url' => route('manageFile')],
            ['label' => __('archive.document_details')],
        ],
    ])
@endsection
@section('content')
    <livewire:document-detail-livewire :document-id="$documentId" />
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endpush
