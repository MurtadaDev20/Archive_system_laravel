<!DOCTYPE html>
<html lang="ar" dir="rtl" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('archive.sign_in') }} — {{ __('archive.app_name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/archive-enterprise.css') }}" rel="stylesheet">
</head>
<body>
    <div class="login-page">
        <div class="col-lg-6 d-none d-lg-flex login-brand-panel">
            <div>
                <div class="mb-4">
                    <i class="bi bi-archive fs-1"></i>
                </div>
                <h1 class="h2 fw-bold mb-3">{{ __('archive.login_title') }}</h1>
                <p class="opacity-75 mb-4">{{ __('archive.login_desc') }}</p>
                <ul class="list-unstyled opacity-75">
                    <li class="mb-2"><i class="bi bi-check-circle me-2"></i>{{ __('archive.login_feature_rbac') }}</li>
                    <li class="mb-2"><i class="bi bi-check-circle me-2"></i>{{ __('archive.login_feature_workflow') }}</li>
                    <li class="mb-2"><i class="bi bi-check-circle me-2"></i>{{ __('archive.login_feature_audit') }}</li>
                </ul>
            </div>
        </div>
        <div class="col-lg-6 login-form-panel">
            <div class="login-form-card">
                <div class="text-center mb-4">
                    <h2 class="h4 fw-bold mb-1">{{ __('archive.welcome_back') }}</h2>
                    <p class="text-archive-muted mb-0">{{ __('archive.sign_in_subtitle') }}</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">{{ __('archive.email_address') }}</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">{{ __('archive.password') }}</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                               name="password" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">{{ __('archive.remember_me') }}</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="small">{{ __('archive.forgot_password') }}</a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-archive-accent w-100 py-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i> {{ __('archive.sign_in') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
