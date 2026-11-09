@extends('layouts.site')

@section('title', $property->title . ' · Flat-LinkBD')
@section('meta_description', Str::limit(strip_tags($property->description), 150))

@section('content')
    @php
        $images = $property->images->pluck('url')->filter()->values();
        $cover = $property->cover_image;
        $location = collect([$property->area?->name, $property->city?->name])->filter()->implode(', ');
        $sellerProfile = $property->seller?->seller;
    @endphp

    <section class="section section-tight detail-top">
        <div class="container">
            <nav class="breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>/</span>
                <a href="{{ route('properties.index') }}">Properties</a>
                <span>/</span>
                <span>{{ Str::limit($property->title, 40) }}</span>
            </nav>

            <div class="detail-header">
                <div>
                    <div class="detail-badges">
                        <span class="badge badge-{{ $property->purpose === 'rental' ? 'rent' : 'sale' }}">
                            {{ $property->purpose === 'rental' ? 'For Rent' : 'For Sale' }}
                        </span>
                        @if ($property->is_featured)<span class="badge badge-gold">Featured</span>@endif
                        @if ($property->verification_status === 'published')<span class="badge badge-verified">Verified</span>@endif
                    </div>
                    <h1>{{ $property->title }}</h1>
                    <p class="detail-location">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 6-9 12-9 12S3 16 3 10a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $property->address }}@if ($location) · {{ $location }}@endif
                    </p>
                </div>
                <div class="detail-price">
                    <span class="price-big">৳{{ number_format((float) $property->price) }}@if ($property->purpose === 'rental')<small>/mo</small>@endif</span>
                    <span class="price-note">{{ $property->price_negotiable ? 'Negotiable' : 'Fixed price' }}</span>
                </div>
            </div>

            <div class="gallery" data-gallery>
                <div class="gallery-main">
                    @if ($cover)
                        <img id="galleryMain" src="{{ $cover }}" alt="{{ $property->title }}">
                    @else
                        <div class="media-placeholder">No image available</div>
                    @endif
                    <div class="gallery-counter">{{ $images->count() ?: 1 }} photos</div>
                </div>
                @if ($images->count())
                    <div class="gallery-thumbs">
                        @foreach ($images as $image)
                            <button type="button" class="gallery-thumb {{ $loop->first ? 'is-active' : '' }}" data-full="{{ $image }}">
                                <img src="{{ $image }}" alt="Photo {{ $loop->iteration }}" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="detail-layout">
                <div class="detail-main">
                    <div class="spec-strip">
                        <div class="spec"><span>Bedrooms</span><strong>{{ $property->bedrooms }}</strong></div>
                        <div class="spec"><span>Bathrooms</span><strong>{{ $property->bathrooms }}</strong></div>
                        <div class="spec"><span>Size</span><strong>{{ number_format((float) $property->size_sqft) }} sqft</strong></div>
                        <div class="spec"><span>Floors</span><strong>{{ $property->floor_no ?? '—' }} / {{ $property->total_floors ?? '—' }}</strong></div>
                        <div class="spec"><span>Condition</span><strong>{{ ucfirst(str_replace('_', ' ', $property->condition)) }}</strong></div>
                        <div class="spec"><span>Views</span><strong>{{ number_format($property->views_count) }}</strong></div>
                    </div>

                    <div class="panel">
                        <h2>About this property</h2>
                        <p class="description">{{ $property->description }}</p>
                    </div>

                    @if ($property->amenities->count())
                        <div class="panel">
                            <h2>Amenities & features</h2>
                            <div class="amenity-grid">
                                @foreach ($property->amenities as $amenity)
                                    <span class="amenity">
                                        <span class="amenity-dot">✓</span>{{ $amenity->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="panel">
                        <h2>Property details</h2>
                        <div class="detail-table">
                            <div><span>Type</span><strong>{{ $property->propertyType?->name ?? '—' }}</strong></div>
                            <div><span>Purpose</span><strong>{{ $property->purpose === 'rental' ? 'Rental' : 'Sale' }}</strong></div>
                            <div><span>Listed</span><strong>{{ $property->created_at->format('M d, Y') }}</strong></div>
                            <div><span>Reference</span><strong>#{{ str_pad($property->id, 5, '0', STR_PAD_LEFT) }}</strong></div>
                        </div>
                    </div>
                </div>

                <aside class="detail-side">
                    <div class="panel seller-card">
                        <div class="seller-head">
                            <span class="avatar">{{ strtoupper(substr($property->seller?->name ?? 'F', 0, 1)) }}</span>
                            <div>
                                <h3>{{ $sellerProfile?->business_name ?? $property->seller?->name }}</h3>
                                <span class="verified-tag">Verified seller</span>
                            </div>
                        </div>
                        <div class="seller-meta">
                            <p><strong>Contact</strong>{{ $sellerProfile?->phone ?? $property->seller?->phone ?? '—' }}</p>
                            <p><strong>Location</strong>{{ $sellerProfile?->address ?? $location }}</p>
                        </div>

                        @auth
                            @if (auth()->user()->isBuyer())
                                <form action="{{ route('buyer.appointments.store') }}" method="POST" class="stack-form">
                                    @csrf
                                    <input type="hidden" name="property_id" value="{{ $property->id }}">
                                    <h4>Book a site visit</h4>
                                    <div class="field">
                                        <label>Preferred date</label>
                                        <input type="date" name="scheduled_date" min="{{ now()->addDay()->toDateString() }}" value="{{ old('scheduled_date') }}" required>
                                    </div>
                                    <div class="field">
                                        <label>Preferred time</label>
                                        <input type="time" name="scheduled_time" value="{{ old('scheduled_time') }}">
                                    </div>
                                    <div class="field">
                                        <label>Notes</label>
                                        <textarea name="buyer_notes" rows="2" placeholder="Anything the seller should know?">{{ old('buyer_notes') }}</textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">Request visit</button>
                                </form>
                            @else
                                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-block">Go to dashboard</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary btn-block">Sign in to book a visit</a>
                        @endauth

                        <a href="{{ route('contact') }}" class="btn btn-outline btn-block">Contact Flat-LinkBD</a>

                        @auth
                            @if (auth()->user()->isBuyer())
                                @if ($isFavorite)
                                    <form action="{{ route('favorites.destroy', $property) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-block">♥ Remove from favorites</button>
                                    </form>
                                @else
                                    <form action="{{ route('favorites.store', $property) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-ghost btn-block">♡ Save to favorites</button>
                                    </form>
                                @endif
                            @endif
                        @endauth
                    </div>
                </aside>
            </div>

            @if ($similar->count())
                <div class="section-head section-head-spaced">
                    <div>
                        <span class="eyebrow eyebrow-dark">You may also like</span>
                        <h2>Similar properties</h2>
                    </div>
                </div>
                <div class="property-grid">
                    @foreach ($similar as $item)
                        @include('partials.property-card', ['property' => $item])
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
