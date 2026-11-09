@extends('layouts.site')

@section('title', 'Flat-LinkBD · Find your next home in Bangladesh')

@section('content')
    <section class="hero">
        <div class="hero-glow hero-glow-1"></div>
        <div class="hero-glow hero-glow-2"></div>
        <div class="container hero-inner">
            <div class="hero-copy reveal">
                <span class="eyebrow">Verified listings · Real people · Zero guesswork</span>
                <h1>Find a home you'll <span class="text-gradient">love to live in.</span></h1>
                <p>Browse verified flats, apartments, duplexes and commercial spaces across Bangladesh — with transparent pricing, rich photos and direct access to trusted sellers.</p>
                <div class="hero-actions">
                    <a href="{{ route('properties.index') }}" class="btn btn-primary btn-lg">Browse properties</a>
                    <a href="{{ route('register') }}" class="btn btn-glass btn-lg">List your property</a>
                </div>
                <div class="hero-stats">
                    <div><strong>{{ number_format($stats['properties']) }}</strong><span>Live listings</span></div>
                    <div><strong>{{ number_format($stats['sellers']) }}</strong><span>Trusted sellers</span></div>
                    <div><strong>{{ number_format($stats['cities']) }}</strong><span>Cities covered</span></div>
                    <div><strong>{{ number_format($stats['closed_deals']) }}</strong><span>Deals closed</span></div>
                </div>
            </div>

            <div class="hero-panel reveal">
                <div class="search-panel">
                    <div class="search-tabs" data-tabs>
                        <button type="button" class="search-tab is-active" data-purpose="sale">Buy</button>
                        <button type="button" class="search-tab" data-purpose="rental">Rent</button>
                    </div>
                    <form action="{{ route('properties.index') }}" method="GET" class="search-form">
                        <input type="hidden" name="purpose" id="searchPurpose" value="sale">
                        <div class="field">
                            <label>Location</label>
                            <select name="city_id">
                                <option value="">Any city</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Property type</label>
                            <select name="property_type_id">
                                <option value="">Any type</option>
                                @foreach ($propertyTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Bedrooms</label>
                            <select name="bedrooms">
                                <option value="">Any</option>
                                <option value="1">1+</option>
                                <option value="2">2+</option>
                                <option value="3">3+</option>
                                <option value="4">4+</option>
                                <option value="5">5+</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Max budget (৳)</label>
                            <input type="number" name="max_price" placeholder="e.g. 15000000" min="0" step="100000">
                        </div>
                        <div class="field field-wide">
                            <label>Keyword</label>
                            <input type="text" name="search" placeholder="Area, address or project name">
                        </div>
                        <button type="submit" class="btn btn-primary search-submit">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                            Search properties
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-head">
                <div>
                    <span class="eyebrow eyebrow-dark">Handpicked</span>
                    <h2>Featured properties</h2>
                    <p>Premium homes selected by our team for quality, location and value.</p>
                </div>
                <a href="{{ route('properties.index') }}" class="btn btn-outline">View all listings</a>
            </div>

            <div class="property-grid">
                @forelse ($featured as $property)
                    @include('partials.property-card', ['property' => $property])
                @empty
                    <div class="empty-state">No featured properties yet.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="section section-muted">
        <div class="container">
            <div class="section-head">
                <div>
                    <span class="eyebrow eyebrow-dark">Neighbourhoods</span>
                    <h2>Explore by city</h2>
                    <p>From the diplomatic enclaves of Dhaka to the port city of Chattogram.</p>
                </div>
            </div>
            <div class="city-grid">
                @foreach ($cities as $city)
                    <a href="{{ route('properties.index', ['city_id' => $city->id]) }}" class="city-card">
                        <span class="city-initial">{{ strtoupper(substr($city->name, 0, 1)) }}</span>
                        <div>
                            <h3>{{ $city->name }}</h3>
                            <span>{{ $city->areas_count }} neighbourhood{{ $city->areas_count === 1 ? '' : 's' }}</span>
                        </div>
                        <span class="city-arrow">&rarr;</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="feature-band">
                <div class="feature-band-copy">
                    <span class="eyebrow">Why Flat-LinkBD</span>
                    <h2>A calmer way to move.</h2>
                    <p>We verify every listing, keep pricing transparent and connect you directly with responsive sellers and agencies.</p>
                </div>
                <div class="feature-band-grid">
                    <div class="feature-item">
                        <span class="feature-icon">✓</span>
                        <h4>Verified listings</h4>
                        <p>Every property is reviewed by our operations team before it goes live.</p>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">৳</span>
                        <h4>Transparent pricing</h4>
                        <p>Clear prices, negotiability and size details on every listing.</p>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">◎</span>
                        <h4>Direct contact</h4>
                        <p>Request visits and message sellers without middlemen.</p>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">★</span>
                        <h4>Curated quality</h4>
                        <p>Rich photography and honest descriptions for confident decisions.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section section-muted">
        <div class="container">
            <div class="section-head">
                <div>
                    <span class="eyebrow eyebrow-dark">Fresh on the market</span>
                    <h2>Latest listings</h2>
                    <p>Newly added homes and commercial spaces, updated daily.</p>
                </div>
                <a href="{{ route('properties.index', ['sort_field' => 'created_at', 'sort_dir' => 'desc']) }}" class="btn btn-outline">See what's new</a>
            </div>
            <div class="property-grid">
                @foreach ($latest as $property)
                    @include('partials.property-card', ['property' => $property])
                @endforeach
            </div>
        </div>
    </section>

    <section class="cta-band">
        <div class="container cta-inner">
            <div>
                <h2>Ready to list your property?</h2>
                <p>Reach thousands of serious buyers and renters. Create an account and publish your first listing in minutes.</p>
            </div>
            <div class="cta-actions">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Create free account</a>
                <a href="{{ route('contact') }}" class="btn btn-glass btn-lg">Talk to us</a>
            </div>
        </div>
    </section>
@endsection
