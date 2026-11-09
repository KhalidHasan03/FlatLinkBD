@php
    $cover = $property->cover_image;
    $location = collect([$property->area?->name, $property->city?->name])->filter()->implode(', ');
    $isFav = auth()->check() && auth()->user()->favorites()->where('property_id', $property->id)->exists();
@endphp
<article class="property-card">
    <a href="{{ route('properties.show', $property->slug) }}" class="property-media">
        @if ($cover)
            <img src="{{ $cover }}" alt="{{ $property->title }}" loading="lazy">
        @else
            <div class="media-placeholder">No image</div>
        @endif
        <div class="property-badges">
            <span class="badge badge-{{ $property->purpose === 'rental' ? 'rent' : 'sale' }}">
                {{ $property->purpose === 'rental' ? 'For Rent' : 'For Sale' }}
            </span>
            @if ($property->is_featured)
                <span class="badge badge-gold">Featured</span>
            @endif
            @if ($property->verification_status === 'published')
                <span class="badge badge-verified">Verified</span>
            @endif
        </div>
    </a>

    <div class="property-body">
        <div class="property-top">
            <span class="property-type">{{ $property->propertyType?->name ?? 'Property' }}</span>
            @auth
                @if (auth()->user()->isBuyer())
                    @if ($isFav)
                        <form action="{{ route('favorites.destroy', $property) }}" method="POST" class="inline-form">
                            @csrf @method('DELETE')
                            <button class="icon-btn is-active" title="Remove from favorites" type="submit">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 21s-7.5-4.6-10-9.2C.3 8.5 2 5 5.6 5c2 0 3.4 1 4.4 2.3C11 6 12.4 5 14.4 5 18 5 19.7 8.5 18 11.8 15.5 16.4 12 21 12 21z"/></svg>
                            </button>
                        </form>
                    @else
                        <form action="{{ route('favorites.store', $property) }}" method="POST" class="inline-form">
                            @csrf
                            <button class="icon-btn" title="Add to favorites" type="submit">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7.5-4.6-10-9.2C.3 8.5 2 5 5.6 5c2 0 3.4 1 4.4 2.3C11 6 12.4 5 14.4 5 18 5 19.7 8.5 18 11.8 15.5 16.4 12 21 12 21z"/></svg>
                            </button>
                        </form>
                    @endif
                @endif
            @endauth
        </div>

        <h3 class="property-title">
            <a href="{{ route('properties.show', $property->slug) }}">{{ $property->title }}</a>
        </h3>

        <p class="property-location">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 6-9 12-9 12S3 16 3 10a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            {{ $location ?: $property->address }}
        </p>

        <div class="property-specs">
            <span title="Bedrooms">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 18v-6h18v6M3 18v2m18-2v2M6 12V7a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v5"/></svg>
                {{ $property->bedrooms }} bd
            </span>
            <span title="Bathrooms">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 12h16v3a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4v-3zM7 12V6a2 2 0 0 1 4 0"/></svg>
                {{ $property->bathrooms }} ba
            </span>
            <span title="Size">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 8V3h5M21 16v5h-5M3 16v5h5M21 8V3h-5"/></svg>
                {{ number_format((float) $property->size_sqft) }} sqft
            </span>
        </div>

        <div class="property-footer">
            <div class="price">
                <span class="currency">৳</span>{{ number_format((float) $property->price) }}@if ($property->purpose === 'rental')<small>/mo</small>@endif
            </div>
            <a href="{{ route('properties.show', $property->slug) }}" class="btn btn-ghost btn-sm">View details</a>
        </div>
    </div>
</article>
