@php
    $currentRoute = Route::currentRouteName();
    $user = Auth::user();
    $isAdmin = $user->hasRole('Admin');
    $isManager = $user->hasRole('Manager');
    $homeRoute = $isAdmin ? route('dashboard') : route('workspace');
@endphp

<aside class="archive-sidebar" id="archiveSidebar" aria-label="{{ __('archive.nav_archive') }}">
    <div class="archive-sidebar-brand">
        <a href="{{ $homeRoute }}" class="d-flex align-items-center gap-2 text-decoration-none">
            <img src="{{ asset('assets/images/FullLogo_Transparent.png') }}" alt="{{ __('archive.app_name') }}" onerror="this.style.display='none'">
            <div>
                <div class="brand-text">{{ __('archive.app_name') }}</div>
                <div class="brand-sub">{{ __('archive.app_subtitle') }}</div>
            </div>
        </a>
    </div>

    <nav class="archive-nav">
        @if($isAdmin)
            <div class="archive-nav-label">{{ __('archive.nav_overview') }}</div>
            <a href="{{ route('dashboard') }}" class="archive-nav-link {{ $currentRoute === 'dashboard' ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>{{ __('archive.nav_dashboard') }}</span>
            </a>
        @else
            <div class="archive-nav-label">{{ __('archive.nav_overview') }}</div>
            <a href="{{ route('workspace') }}" class="archive-nav-link {{ $currentRoute === 'workspace' ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>{{ __('archive.workspace_dashboard') }}</span>
            </a>
        @endif

        <div class="archive-nav-label">{{ __('archive.nav_archive') }}</div>

        @if($isAdmin)
            <a href="{{ route('departments') }}" class="archive-nav-link {{ $currentRoute === 'departments' ? 'active' : '' }}">
                <i class="bi bi-building"></i>
                <span>{{ __('archive.nav_departments') }}</span>
            </a>
        @endif

        <a href="{{ route('folders') }}" class="archive-nav-link {{ $currentRoute === 'folders' ? 'active' : '' }}">
            <i class="bi bi-folder2-open"></i>
            <span>{{ __('archive.nav_folders') }}</span>
        </a>

        @can('create', \App\Models\File::class)
            <a href="{{ route('addFile') }}" class="archive-nav-link {{ $currentRoute === 'addFile' ? 'active' : '' }}">
                <i class="bi bi-cloud-upload"></i>
                <span>{{ __('archive.nav_upload') }}</span>
            </a>
        @endcan

        <a href="{{ route('manageFile') }}" class="archive-nav-link {{ in_array($currentRoute, ['manageFile', 'manageFileShow', 'viewFile', 'document.show']) ? 'active' : '' }}">
            <i class="bi bi-files"></i>
            <span>{{ __('archive.nav_documents') }}</span>
            @if(!empty($sidebarCounts['documents']) && $sidebarCounts['documents'] > 0)
                <span id="sidebar-documents-badge" class="archive-nav-badge" title="{{ __('archive.pending_tasks') }}">{{ $sidebarCounts['documents'] > 99 ? '99+' : $sidebarCounts['documents'] }}</span>
            @else
                <span id="sidebar-documents-badge" class="archive-nav-badge d-none" title="{{ __('archive.pending_tasks') }}">0</span>
            @endif
        </a>

        @if($isAdmin)
            <div class="archive-nav-label mt-2">{{ __('archive.nav_settings') }}</div>
            <a href="{{ route('taxonomy') }}" class="archive-nav-link {{ $currentRoute === 'taxonomy' ? 'active' : '' }}">
                <i class="bi bi-sliders"></i>
                <span>{{ __('archive.manage_taxonomy') }}</span>
            </a>
        @endif

        @if($isAdmin || $isManager)
            <div class="archive-nav-label mt-2">{{ __('archive.nav_administration') }}</div>
            <a href="{{ route('allUsers') }}" class="archive-nav-link {{ $currentRoute === 'allUsers' ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>{{ __('archive.nav_users') }}</span>
            </a>
        @endif
    </nav>

    <div class="p-3 border-top border-light border-opacity-10">
        <small class="text-white-50 d-block">{{ __('archive.signed_in_as') }}</small>
        <strong class="text-white small">{{ $user->name }}</strong>
    </div>
</aside>
