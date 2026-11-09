<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard · Flat-LinkBD')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
</head>
<body class="dash-body">
    @php
        $user = auth()->user();
        $roleLabel = $user->isSuperAdmin() ? 'Super Admin' : ($user->isStaff() ? 'Staff' : ucfirst($user->role));
    @endphp

    <div class="dash-shell">
        <aside class="dash-sidebar" id="dashSidebar">
            <a href="{{ route('home') }}" class="brand brand-dash">
                <span class="brand-mark">F</span>
                <span class="brand-text">Flat<span>Link</span><small>BD</small></span>
            </a>

            <div class="dash-role">
                <span class="avatar avatar-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                <div>
                    <strong>{{ Str::limit($user->name, 18) }}</strong>
                    <span>{{ $roleLabel }}</span>
                </div>
            </div>

            <nav class="dash-nav">
                @yield('sidebar')
            </nav>

            <div class="dash-sidebar-foot">
                <a href="{{ route('properties.index') }}" class="dash-link">View marketplace</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dash-link dash-logout">Sign out</button>
                </form>
            </div>
        </aside>

        <div class="dash-main">
            <header class="dash-topbar">
                <button class="dash-toggle" type="button" id="dashToggle" aria-label="Toggle sidebar">
                    <span></span><span></span><span></span>
                </button>
                <div class="dash-heading">
                    <h1>@yield('dash_title', 'Dashboard')</h1>
                    <p>@yield('dash_subtitle', 'Welcome back to Flat-LinkBD')</p>
                </div>
                <div class="dash-user">
                    <span class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <div>
                        <strong>{{ $user->name }}</strong>
                        <span>{{ $roleLabel }}</span>
                    </div>
                </div>
            </header>

            @include('partials.flash')

            <div class="dash-content">
                @yield('content')
            </div>
        </div>
    </div>

    <div class="dash-overlay" id="dashOverlay"></div>

    <script src="{{ asset('js/site.js') }}"></script>
    @stack('scripts')
</body>
</html>
