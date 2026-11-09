@extends('layouts.dashboard')

@section('title', 'Seller Dashboard · Flat-LinkBD')
@section('dash_title', 'Seller workspace')
@section('dash_subtitle', 'Manage your listings, visits and deals')

@section('sidebar')
    <a href="#overview" class="dash-link is-active">Overview</a>
    <a href="#listings" class="dash-link">My listings</a>
    <a href="#appointments" class="dash-link">Visit requests</a>
    <a href="#deals" class="dash-link">Deals</a>
    <a href="#new-listing" class="dash-link">Post a listing</a>
@endsection

@section('content')
    <section id="overview" class="dash-section">
        <div class="stat-grid">
            <div class="stat-card"><span>Total listings</span><strong>{{ $stats['total_properties'] }}</strong></div>
            <div class="stat-card"><span>Published</span><strong>{{ $stats['published'] }}</strong></div>
            <div class="stat-card"><span>Awaiting review</span><strong>{{ $stats['pending'] }}</strong></div>
            <div class="stat-card"><span>Active deals</span><strong>{{ $stats['active_deals'] }}</strong></div>
            <div class="stat-card"><span>Confirmed visits</span><strong>{{ $stats['upcoming_appointments'] }}</strong></div>
            <div class="stat-card"><span>Total views</span><strong>{{ number_format($stats['total_views']) }}</strong></div>
        </div>

        <div class="quick-actions">
            <a href="#new-listing" class="quick-action"><span>＋</span> Post a new listing</a>
            <a href="#appointments" class="quick-action"><span>◷</span> Review visit requests</a>
            <a href="{{ route('properties.index') }}" class="quick-action"><span>◎</span> Browse marketplace</a>
        </div>
    </section>

    <section id="listings" class="dash-section">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>My listings</h2>
                    <p class="muted">Track status, views and verification for every property.</p>
                </div>
                <a href="#new-listing" class="btn btn-primary btn-sm">New listing</a>
            </div>

            @if ($properties->count())
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Verification</th>
                                <th>Views</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($properties as $property)
                                <tr>
                                    <td>
                                        <div class="table-property">
                                            @if ($property->cover_image)
                                                <img src="{{ $property->cover_image }}" alt="" class="table-thumb">
                                            @else
                                                <span class="table-thumb placeholder">—</span>
                                            @endif
                                            <div>
                                                <strong>{{ $property->title }}</strong>
                                                <span>{{ $property->area?->name ?? '—' }} · {{ $property->propertyType?->name ?? 'Property' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>৳{{ number_format((float) $property->price) }}</td>
                                    <td><span class="badge badge-status-{{ $property->status }}">{{ ucfirst($property->status) }}</span></td>
                                    <td><span class="badge badge-verify-{{ $property->verification_status }}">{{ ucfirst($property->verification_status) }}</span></td>
                                    <td>{{ number_format($property->views_count) }}</td>
                                    <td class="text-right">
                                        <div class="row-actions">
                                            @if ($property->status === 'published')
                                                <a href="{{ route('properties.show', $property->slug) }}" class="btn btn-ghost btn-xs">View</a>
                                            @endif
                                            <details class="inline-details">
                                                <summary class="btn btn-outline btn-xs">Edit</summary>
                                                <div class="inline-panel">
                                                    <form action="{{ route('seller.properties.update', $property) }}" method="POST" class="stack-form">
                                                        @csrf @method('PATCH')
                                                        <div class="field">
                                                            <label>Title</label>
                                                            <input type="text" name="title" value="{{ $property->title }}" required>
                                                        </div>
                                                        <div class="field-row">
                                                            <div class="field">
                                                                <label>Price</label>
                                                                <input type="number" name="price" value="{{ (float) $property->price }}" min="0" step="1000" required>
                                                            </div>
                                                            <div class="field">
                                                                <label>Status</label>
                                                                <select name="status">
                                                                    <option value="draft" @selected($property->status === 'draft')>Draft</option>
                                                                    <option value="archived" @selected($property->status === 'archived')>Archived</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="field">
                                                            <label>Description</label>
                                                            <textarea name="description" rows="3">{{ $property->description }}</textarea>
                                                        </div>
                                                        <button type="submit" class="btn btn-primary btn-sm">Save changes</button>
                                                    </form>

                                                    <form action="{{ route('seller.properties.images', $property) }}" method="POST" enctype="multipart/form-data" class="stack-form">
                                                        @csrf
                                                        <div class="field">
                                                            <label>Add photos</label>
                                                            <input type="file" name="images[]" accept="image/*" multiple>
                                                        </div>
                                                        <button type="submit" class="btn btn-outline btn-sm">Upload images</button>
                                                    </form>
                                                </div>
                                            </details>
                                            <form action="{{ route('seller.properties.destroy', $property) }}" method="POST" onsubmit="return confirm('Delete this listing permanently?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pagination-wrap">{{ $properties->links() }}</div>
            @else
                <div class="empty-state">
                    <h3>No listings yet</h3>
                    <p>Publish your first property to start receiving visit requests.</p>
                    <a href="#new-listing" class="btn btn-primary">Post a listing</a>
                </div>
            @endif
        </div>
    </section>

    <section id="appointments" class="dash-section">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Visit requests</h2>
                    <p class="muted">Confirm, complete or cancel buyer visits.</p>
                </div>
            </div>

            @if ($appointments->count())
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr><th>Property</th><th>Buyer</th><th>When</th><th>Status</th><th class="text-right">Actions</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($appointments as $appointment)
                                <tr>
                                    <td>{{ $appointment->property?->title ?? '—' }}</td>
                                    <td>
                                        <strong>{{ $appointment->buyer?->name ?? '—' }}</strong>
                                        <span class="muted block">{{ $appointment->buyer?->phone }}</span>
                                    </td>
                                    <td>
                                        {{ $appointment->scheduled_date?->format('M d, Y') }}
                                        <span class="muted block">{{ $appointment->scheduled_time ?? 'Flexible' }}</span>
                                    </td>
                                    <td><span class="badge badge-appt-{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span></td>
                                    <td class="text-right">
                                        <div class="row-actions">
                                            @foreach (['confirmed' => 'Confirm', 'completed' => 'Complete', 'cancelled' => 'Cancel'] as $status => $label)
                                                @if ($appointment->status !== $status)
                                                    <form action="{{ route('seller.appointments.update', $appointment) }}" method="POST">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ $status }}">
                                                        <button type="submit" class="btn {{ $status === 'cancelled' ? 'btn-danger' : 'btn-outline' }} btn-xs">{{ $label }}</button>
                                                    </form>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state"><p>No visit requests yet.</p></div>
            @endif
        </div>
    </section>

    <section id="deals" class="dash-section">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Deals</h2>
                    <p class="muted">Monitor negotiation progress and closed deals.</p>
                </div>
            </div>

            @if ($deals->count())
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr><th>Property</th><th>Buyer</th><th>Stage</th><th>Agreed price</th><th>Commission</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($deals as $deal)
                                <tr>
                                    <td>{{ $deal->property?->title ?? '—' }}</td>
                                    <td>{{ $deal->buyer?->name ?? '—' }}</td>
                                    <td><span class="badge badge-stage-{{ $deal->stage }}">{{ ucfirst(str_replace('_', ' ', $deal->stage)) }}</span></td>
                                    <td>{{ $deal->agreed_price ? '৳' . number_format((float) $deal->agreed_price) : '—' }}</td>
                                    <td>{{ $deal->commission_amount ? '৳' . number_format((float) $deal->commission_amount) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state"><p>No deals yet.</p></div>
            @endif
        </div>
    </section>

    <section id="new-listing" class="dash-section">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Post a new listing</h2>
                    <p class="muted">Submitted listings enter verification and go live once approved.</p>
                </div>
            </div>

            <form action="{{ route('seller.properties.store') }}" method="POST" enctype="multipart/form-data" class="stack-form">
                @csrf
                <div class="field">
                    <label>Listing title</label>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Bright 3 Bed Apartment in Dhanmondi" required>
                </div>
                <div class="field">
                    <label>Description</label>
                    <textarea name="description" rows="4" required placeholder="Describe the property, finishes, connectivity and highlights.">{{ old('description') }}</textarea>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label>Purpose</label>
                        <select name="purpose" required>
                            <option value="sale" @selected(old('purpose') === 'sale')>For sale</option>
                            <option value="rental" @selected(old('purpose') === 'rental')>For rent</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Property type</label>
                        <select name="property_type_id">
                            <option value="">Select type</option>
                            @foreach ($propertyTypes as $type)
                                <option value="{{ $type->id }}" @selected((string) old('property_type_id') === (string) $type->id)>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Condition</label>
                        <select name="condition" required>
                            @foreach (['new' => 'New', 'under_construction' => 'Under construction', 'renovated' => 'Renovated', 'old' => 'Old'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('condition') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label>Price (৳)</label>
                        <input type="number" name="price" value="{{ old('price') }}" min="0" step="1000" required>
                    </div>
                    <div class="field">
                        <label>Size (sqft)</label>
                        <input type="number" name="size_sqft" value="{{ old('size_sqft') }}" min="0" step="10" required>
                    </div>
                    <div class="field">
                        <label>Price negotiable?</label>
                        <select name="price_negotiable">
                            <option value="1" @selected(old('price_negotiable', '1') === '1')>Yes</option>
                            <option value="0" @selected(old('price_negotiable') === '0')>No</option>
                        </select>
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label>Bedrooms</label>
                        <input type="number" name="bedrooms" value="{{ old('bedrooms', 0) }}" min="0" required>
                    </div>
                    <div class="field">
                        <label>Bathrooms</label>
                        <input type="number" name="bathrooms" value="{{ old('bathrooms', 0) }}" min="0" required>
                    </div>
                    <div class="field">
                        <label>Floor no.</label>
                        <input type="number" name="floor_no" value="{{ old('floor_no') }}" min="0">
                    </div>
                    <div class="field">
                        <label>Total floors</label>
                        <input type="number" name="total_floors" value="{{ old('total_floors') }}" min="0">
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label>City</label>
                        <select name="city_id" id="sellerCity">
                            <option value="">Select city</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" @selected((string) old('city_id') === (string) $city->id)>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Area</label>
                        <select name="area_id" id="sellerArea">
                            <option value="">Select area</option>
                            @foreach ($cities as $city)
                                @foreach ($city->areas as $area)
                                    <option value="{{ $area->id }}" data-city="{{ $city->id }}" @selected((string) old('area_id') === (string) $area->id)>{{ $area->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label>Address</label>
                    <input type="text" name="address" value="{{ old('address') }}" placeholder="House, road, block, locality" required>
                </div>

                <div class="field">
                    <label>Amenities</label>
                    <div class="chip-grid">
                        @foreach ($amenities as $amenity)
                            <label class="chip">
                                <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}" @checked(in_array($amenity->id, old('amenities', [])))>
                                <span>{{ $amenity->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="field">
                    <label>Photos (up to 8)</label>
                    <input type="file" name="images[]" accept="image/*" multiple>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">Submit listing for review</button>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    (function () {
        var city = document.getElementById('sellerCity');
        var area = document.getElementById('sellerArea');
        if (!city || !area) return;
        function filterAreas() {
            var value = city.value;
            Array.prototype.forEach.call(area.options, function (opt) {
                if (!opt.value) return;
                var match = !value || opt.dataset.city === value;
                opt.hidden = !match;
                if (!match && opt.selected) area.value = '';
            });
        }
        city.addEventListener('change', filterAreas);
        filterAreas();
    })();
</script>
@endpush
