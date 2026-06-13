@extends('layouts.master')
@section('title', __('archive.departments') . ' — ' . __('archive.app_name'))
@section('page-header')
    @include('layouts.partials.page-header', [
        'title' => __('archive.departments'),
        'subtitle' => __('archive.departments_subtitle'),
        'breadcrumbs' => [
            ['label' => __('archive.home'), 'url' => $homeRoute],
            ['label' => __('archive.departments')],
        ],
    ])
@endsection
@section('content')
    <livewire:department-livewire />
@endsection
