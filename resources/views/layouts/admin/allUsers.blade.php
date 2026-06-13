@extends('layouts.master')
@section('title', __('archive.users_management') . ' — ' . __('archive.app_name'))
@section('page-header')
    @include('layouts.partials.page-header', [
        'title' => __('archive.users_management'),
        'subtitle' => __('archive.users_subtitle'),
        'breadcrumbs' => [
            ['label' => __('archive.home'), 'url' => $homeRoute],
            ['label' => __('archive.nav_users')],
        ],
    ])
@endsection
@section('content')
    <livewire:userslivewire />
@endsection
