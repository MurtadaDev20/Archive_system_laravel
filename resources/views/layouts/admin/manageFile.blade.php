@extends('layouts.master')
@section('title', __('archive.manage_documents') . ' — ' . __('archive.app_name'))
@section('page-header')
    @include('layouts.partials.page-header', [
        'title' => __('archive.manage_documents'),
        'subtitle' => __('archive.manage_documents_subtitle'),
        'breadcrumbs' => [
            ['label' => __('archive.home'), 'url' => $homeRoute],
            ['label' => __('archive.documents')],
        ],
    ])
@endsection
@section('content')
    <livewire:manage-file-livewire />
@endsection
