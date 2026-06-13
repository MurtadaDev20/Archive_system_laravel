@extends('layouts.master')
@section('title', __('archive.upload_document') . ' — ' . __('archive.app_name'))
@section('page-header')
    @include('layouts.partials.page-header', [
        'title' => __('archive.upload_document'),
        'subtitle' => __('archive.upload_subtitle'),
        'breadcrumbs' => [
            ['label' => __('archive.home'), 'url' => $homeRoute],
            ['label' => __('archive.documents'), 'url' => route('manageFile')],
            ['label' => __('archive.upload_document')],
        ],
    ])
@endsection
@section('content')
    <livewire:file-livewire />
@endsection
