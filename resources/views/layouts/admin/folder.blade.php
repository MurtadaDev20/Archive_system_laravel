@extends('layouts.master')
@section('title', __('archive.folders') . ' — ' . __('archive.app_name'))
@section('page-header')
    @include('layouts.partials.page-header', [
        'title' => __('archive.folders'),
        'subtitle' => __('archive.folders_subtitle'),
        'breadcrumbs' => [
            ['label' => __('archive.home'), 'url' => $homeRoute],
            ['label' => __('archive.folders')],
        ],
    ])
@endsection
@section('content')
    <livewire:folder-livewire />
@endsection
