<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Flat-LinkBD · Premium Property Marketplace')</title>
    <meta name="description" content="@yield('meta_description', 'Discover verified flats, apartments, duplexes and commercial spaces across Bangladesh.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    @stack('head')
</head>
<body class="site-body">
    <header class="site-header" id="siteHeader">
        <div class="container nav-wrap">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-mark">F</span>
                <span class="brand-text">Flat<span>Link</span><small>BD</small></span>
            </a>

            <button class="nav-toggle" type="button" aria-label="Toggle navigation" id="navToggle">
                <span></span><span></span><span></span>
            </button>

            <nav class="site-nav" id="siteNav">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}">Home</a>
                <a href="{{ route('properties.index') }}" class="nav-link {{ request()->routeIs('properties.*') ? 'is-active' : '' }}">Properties</a>
                <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'is-active' : '' }}">Contact</a>

                @auth
                    <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
                    <div class="nav-user">
                        <span class="avatar avatar-sm">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="nav-user-name">{{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="inline-form">
                            @csrf
                            <button type="submit" class="nav-link nav-logout">Sign out</button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="nav-link">Sign in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Get started</a>
                @endauth
            </nav>
        </div>
    </header>

    @include('partials.flash')

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <a href="{{ route('home') }}" class="brand brand-footer">
                    <span class="brand-mark">F</span>
                    <span class="brand-text">Flat<span>Link</span><small>BD</small></span>
                </a>
                <p class="footer-copy">The trusted marketplace for verified flats, apartments and commercial spaces across Bangladesh.</p>
            </div>
            <div>
                <h4>Explore</h4>
                <a href="{{ route('properties.index') }}">All properties</a>
                <a href="{{ route('properties.index', ['purpose' => 'sale']) }}">For sale</a>
                <a href="{{ route('properties.index', ['purpose' => 'rental']) }}">For rent</a>
            </div>
            <div>
                <h4>Company</h4>
                <a href="{{ route('contact') }}">Contact us</a>
                <a href="{{ route('register') }}">List a property</a>
                <a href="{{ route('login') }}">Sign in</a>
            </div>
            <div>
                <h4>Get in touch</h4>
                <p>Gulshan Avenue, Dhaka 1212</p>
                <p>+880 1700 000000</p>
                <p>hello@flatlinkbd.com</p>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <span>&copy; {{ date('Y') }} Flat-LinkBD. All rights reserved.</span>
                <span>Built for buyers, sellers and agencies.</span>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/site.js') }}"></script>
    @stack('scripts')
</body>
</html>
