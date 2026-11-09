@extends('layouts.site')

@section('title', 'Browse Properties · Flat-LinkBD')

@section('content')
    <section class="page-hero">
        <div class="container">
            <span class="eyebrow">Explore the market</span>
            <h1>Find your next address.</h1>
            <p>{{ number_format($properties->total()) }} verified {{ Str::plural('property', $properties->total()) }} available across Bangladesh.</p>
        </div>
    </section>

    <section class="section section-tight">
        <div class="container listings-layout">
            <aside class="filters reveal">
                <div class="filters-head">
                    <h2>Filters</h2>
                    <a href="{{ route('properties.index') }}" class="filters-reset">Reset</a>
                </div>
                <form action="{{ route('properties.index') }}" method="GET" class="filter-form">
                    <div class="field">
                        <label>Keyword</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Area, address, title">
                    </div>

                    <div class="field">
                        <label>Purpose</label>
                        <select name="purpose">
                            <option value="">Buy or rent</option>
                            <option value="sale" @selected(($filters['purpose'] ?? '') === 'sale')>For sale</option>
                            <option value="rental" @selected(($filters['purpose'] ?? '') === 'rental')>For rent</option>
                        </select>
                    </div>

                    <div class="field">
                        <label>City</label>
                        <select name="city_id">
                            <option value="">Any city</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" @selected((string)($filters['city_id'] ?? '') === (string)$city->id)>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Area</label>
                        <select name="area_id">
                            <option value="">Any area</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->id }}" @selected((string)($filters['area_id'] ?? '') === (string)$area->id)>{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Property type</label>
                        <select name="property_type_id">
                            <option value="">Any type</option>
                            @foreach ($propertyTypes as $type)
                                <option value="{{ $type->id }}" @selected((string)($filters['property_type_id'] ?? '') === (string)$type->id)>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label>Bedrooms</label>
                            <select name="bedrooms">
                                <option value="">Any</option>
                                @foreach ([1,2,3,4,5] as $n)
                                    <option value="{{ $n }}" @selected((string)($filters['bedrooms'] ?? '') === (string)$n)>{{ $n }}+</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Bathrooms</label>
                            <select name="bathrooms">
                                <option value="">Any</option>
                                @foreach ([1,2,3,4] as $n)
                                    <option value="{{ $n }}" @selected((string)($filters['bathrooms'] ?? '') === (string)$n)>{{ $n }}+</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label>Min price</label>
                            <input type="number" name="min_price" min="0" step="100000" value="{{ $filters['min_price'] ?? '' }}" placeholder="0">
                        </div>
                        <div class="field">
                            <label>Max price</label>
                            <input type="number" name="max_price" min="0" step="100000" value="{{ $filters['max_price'] ?? '' }}" placeholder="Any">
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label>Min size (sqft)</label>
                            <input type="number" name="min_size" min="0" step="50" value="{{ $filters['min_size'] ?? '' }}" placeholder="0">
                        </div>
                        <div class="field">
                            <label>Max size (sqft)</label>
                            <input type="number" name="max_size" min="0" step="50" value="{{ $filters['max_size'] ?? '' }}" placeholder="Any">
                        </div>
                    </div>

                    <input type="hidden" name="sort_field" value="{{ $filters['sort_field'] ?? '' }}">
                    <input type="hidden" name="sort_dir" value="{{ $filters['sort_dir'] ?? '' }}">

                    <button type="submit" class="btn btn-primary btn-block">Apply filters</button>
                </form>
            </aside>

            <div class="listings-main">
                <div class="listings-toolbar">
                    <form action="{{ route('properties.index') }}" method="GET" class="sort-form">
                        @foreach ($filters as $key => $value)
                            @if (!in_array($key, ['sort_field', 'sort_dir', 'per_page']) && $value !== null && $value !== '')
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label for="sortField">Sort by</label>
                        <select name="sort_field" id="sortField" onchange="this.form.submit()">
                            <option value="created_at" @selected(($filters['sort_field'] ?? 'created_at') === 'created_at')>Newest</option>
                            <option value="price" @selected(($filters['sort_field'] ?? '') === 'price')>Price</option>
                            <option value="size_sqft" @selected(($filters['sort_field'] ?? '') === 'size_sqft')>Size</option>
                            <option value="bedrooms" @selected(($filters['sort_field'] ?? '') === 'bedrooms')>Bedrooms</option>
                            <option value="views_count" @selected(($filters['sort_field'] ?? '') === 'views_count')>Popularity</option>
                        </select>
                        <select name="sort_dir" onchange="this.form.submit()">
                            <option value="desc" @selected(($filters['sort_dir'] ?? 'desc') === 'desc')>Descending</option>
                            <option value="asc" @selected(($filters['sort_dir'] ?? '') === 'asc')>Ascending</option>
                        </select>
                    </form>
                </div>

                @if ($properties->count())
                    <div class="property-grid property-grid-2">
                        @foreach ($properties as $property)
                            @include('partials.property-card', ['property' => $property])
                        @endforeach
                    </div>

                    <div class="pagination-wrap">
                        {{ $properties->links() }}
                    </div>
                @else
                    <div class="empty-state">
                        <h3>No properties match your filters</h3>
                        <p>Try widening your price range or clearing a few filters.</p>
                        <a href="{{ route('properties.index') }}" class="btn btn-primary">Clear filters</a>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
