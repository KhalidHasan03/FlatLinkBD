@extends('layouts.site')

@section('title', 'Sign in · Flat-LinkBD')

@section('content')
    <section class="auth-section">
        <div class="container auth-layout">
            <div class="auth-aside">
                <span class="eyebrow">Welcome back</span>
                <h1>Your next move starts here.</h1>
                <p>Sign in to manage listings, track visit requests and save the homes you love.</p>
                <div class="auth-points">
                    <span>✓ Save favorites across devices</span>
                    <span>✓ Book and manage site visits</span>
                    <span>✓ Track deals and documents</span>
                </div>
            </div>

            <div class="auth-card">
                <h2>Sign in</h2>
                <p class="muted">Enter your account details to continue.</p>

                <form action="{{ route('login.attempt') }}" method="POST" class="stack-form">
                    @csrf
                    <div class="field">
                        <label>Email address</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                    </div>
                    <div class="field">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                    <label class="check">
                        <input type="checkbox" name="remember" value="1">
                        <span>Keep me signed in</span>
                    </label>
                    <button type="submit" class="btn btn-primary btn-block btn-lg">Sign in</button>
                </form>

                <p class="auth-switch">New to Flat-LinkBD? <a href="{{ route('register') }}">Create an account</a></p>

                <div class="demo-box">
                    <h4>Demo accounts</h4>
                    <div><span>Admin</span><code>admin@flatlinkbd.com</code></div>
                    <div><span>Seller</span><code>rahman@flatlinkbd.com</code></div>
                    <div><span>Buyer</span><code>ayesha@example.com</code></div>
                    <p>Password for all demo accounts: <code>password</code></p>
                </div>
            </div>
        </div>
    </section>
@endsection
