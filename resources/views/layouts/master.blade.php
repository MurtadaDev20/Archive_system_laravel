<!DOCTYPE html>
<html lang="ar" dir="rtl" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="user-id" content="{{ Auth::id() }}">
        @php
            $teamManagerId = Auth::user()->hasRole('Manager')
                ? Auth::id()
                : Auth::user()->manager_id;
        @endphp
        @if($teamManagerId)
            <meta name="team-manager-id" content="{{ $teamManagerId }}">
        @endif
    @endauth
    @include('layouts.head')
</head>

<body class="archive-app">

    <div id="pre-loader" aria-hidden="true">
        <div class="spinner-border text-success" role="status">
            <span class="visually-hidden">{{ __('archive.loading') }}</span>
        </div>
    </div>

    <div class="archive-sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="archive-shell">
        @include('layouts.main-sidebar')

        <div class="archive-main">
            @include('layouts.main-header')

            <main class="archive-content" role="main">
                @yield('page-header')
                @yield('content')
            </main>

            @include('layouts.footer')
        </div>
    </div>

    @include('layouts.footer-scripts')
    @auth
        <livewire:archive-realtime-livewire />
    @endauth
    @stack('scripts')
</body>

</html>
