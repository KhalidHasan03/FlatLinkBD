@extends('layouts.site')

@section('title', 'Contact · Flat-LinkBD')

@section('content')
    <section class="page-hero">
        <div class="container">
            <span class="eyebrow">We're here to help</span>
            <h1>Let's talk property.</h1>
            <p>Questions about a listing, verification or partnership? Send us a message and our team will get back within one business day.</p>
        </div>
    </section>

    <section class="section section-tight">
        <div class="container contact-layout">
            <div class="contact-info reveal">
                <div class="contact-card">
                    <span class="contact-icon">◎</span>
                    <h3>Visit the office</h3>
                    <p>Gulshan Avenue, Dhaka 1212<br>Bangladesh</p>
                </div>
                <div class="contact-card">
                    <span class="contact-icon">✆</span>
                    <h3>Call us</h3>
                    <p>+880 1700 000000<br>Sat–Thu · 9am–7pm</p>
                </div>
                <div class="contact-card">
                    <span class="contact-icon">✉</span>
                    <h3>Email</h3>
                    <p>hello@flatlinkbd.com<br>support@flatlinkbd.com</p>
                </div>
                <div class="contact-note">
                    <h4>Thinking of listing?</h4>
                    <p>Create a free seller account to publish verified listings and manage visit requests.</p>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Become a seller</a>
                </div>
            </div>

            <div class="contact-form-wrap reveal">
                <div class="panel">
                    <h2>Send a message</h2>
                    <p class="muted">All fields are required.</p>
                    <form action="{{ route('contact.submit') }}" method="POST" class="stack-form">
                        @csrf
                        <div class="field-row">
                            <div class="field">
                                <label>Full name</label>
                                <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required>
                            </div>
                            <div class="field">
                                <label>Email</label>
                                <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                            </div>
                        </div>
                        <div class="field">
                            <label>Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" required>
                        </div>
                        <div class="field">
                            <label>Message</label>
                            <textarea name="message" rows="6" required placeholder="Tell us how we can help">{{ old('message') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">Send message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
