@extends('layouts.site')

@section('title', 'Create account · Flat-LinkBD')

@section('content')
    <section class="auth-section">
        <div class="container auth-layout">
            <div class="auth-aside">
                <span class="eyebrow">Join Flat-LinkBD</span>
                <h1>Buy smarter. Sell faster.</h1>
                <p>Create a free account to save properties, request visits or publish verified listings in minutes.</p>
                <div class="auth-points">
                    <span>✓ Verified, photo-rich listings</span>
                    <span>✓ Direct seller communication</span>
                    <span>✓ Simple, transparent workflow</span>
                </div>
            </div>

            <div class="auth-card">
                <h2>Create your account</h2>
                <p class="muted">Choose how you want to use Flat-LinkBD.</p>

                <form action="{{ route('register.attempt') }}" method="POST" class="stack-form">
                    @csrf
                    <div class="role-select">
                        <label class="role-option">
                            <input type="radio" name="role" value="buyer" @checked(old('role', 'buyer') === 'buyer')>
                            <span class="role-body">
                                <strong>I'm a buyer</strong>
                                <small>Browse, save and book visits</small>
                            </span>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="seller" @checked(old('role') === 'seller')>
                            <span class="role-body">
                                <strong>I'm a seller</strong>
                                <small>List properties and manage deals</small>
                            </span>
                        </label>
                    </div>

                    <div class="field">
                        <label>Full name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label>Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required>
                        </div>
                        <div class="field">
                            <label>Phone</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" required>
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label>Password</label>
                            <input type="password" name="password" required>
                        </div>
                        <div class="field">
                            <label>Confirm password</label>
                            <input type="password" name="password_confirmation" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">Create account</button>
                </form>

                <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
            </div>
        </div>
    </section>
@endsection
