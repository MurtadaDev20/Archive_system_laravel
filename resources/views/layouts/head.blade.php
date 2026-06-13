<title>@yield('title', __('archive.app_full_name'))</title>
<meta name="description" content="{{ __('archive.app_full_name') }}" />
<link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}" type="image/x-icon" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="{{ asset('assets/css/plugins/toastr.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/archive-enterprise.css') }}" rel="stylesheet">
@yield('css')
@livewireStyles
