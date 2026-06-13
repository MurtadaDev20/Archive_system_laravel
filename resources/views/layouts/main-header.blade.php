<header class="archive-header">
    <div class="d-flex align-items-center gap-3">
        <button type="button" class="btn btn-outline-secondary btn-sm d-lg-none" id="sidebarToggle" aria-label="{{ __('archive.nav_archive') }}">
            <i class="bi bi-list"></i>
        </button>
        <form class="d-none d-md-block" role="search" onsubmit="return false;">
            <div class="input-group input-group-sm" style="min-width: 280px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-archive-muted"></i></span>
                <input type="search" class="form-control border-start-0" placeholder="{{ __('archive.quick_search') }}" aria-label="{{ __('archive.quick_search') }}" disabled>
            </div>
        </form>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="themeToggle" title="{{ __('archive.dark_mode') }}">
            <i class="bi bi-moon-stars"></i>
        </button>
        <div class="dropdown">
            <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:0.8rem;font-weight:600;">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </span>
                <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-semibold">{{ Auth::user()->name }}</div>
                    <small class="text-archive-muted">{{ Auth::user()->email }}</small>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-2"></i>{{ __('archive.nav_logout') }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>
