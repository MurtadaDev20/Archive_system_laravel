@extends('layouts.master')

@section('title', __('archive.manage_taxonomy') . ' — ' . __('archive.app_name'))

@section('page-header')
    @include('layouts.partials.page-header', [
        'title' => __('archive.manage_taxonomy'),
        'subtitle' => __('archive.manage_taxonomy_subtitle'),
        'breadcrumbs' => [
            ['label' => __('archive.home'), 'url' => route('dashboard')],
            ['label' => __('archive.manage_taxonomy')],
        ],
    ])
@endsection

@section('content')
    @livewire('taxonomy-livewire')
@endsection
