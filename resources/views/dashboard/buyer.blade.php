@extends('layouts.dashboard')

@section('title', 'Buyer Dashboard · Flat-LinkBD')
@section('dash_title', 'Buyer workspace')
@section('dash_subtitle', 'Your saved homes, visits and deals')

@section('sidebar')
    <a href="#overview" class="dash-link is-active">Overview</a>
    <a href="#favorites" class="dash-link">Saved properties</a>
    <a href="#appointments" class="dash-link">My visits</a>
    <a href="#deals" class="dash-link">Deals</a>
    <a href="#recommended" class="dash-link">Recommended</a>
@endsection

@section('content')
    <section id="overview" class="dash-section">
        <div class="stat-grid">
            <div class="stat-card"><span>Saved properties</span><strong>{{ $stats['favorites'] }}</strong></div>
            <div class="stat-card"><span>Total visit requests</span><strong>{{ $stats['appointments'] }}</strong></div>
            <div class="stat-card"><span>Confirmed visits</span><strong>{{ $stats['confirmed'] }}</strong></div>
            <div class="stat-card"><span>Active deals</span><strong>{{ $stats['active_deals'] }}</strong></div>
        </div>

        <div class="quick-actions">
            <a href="{{ route('properties.index') }}" class="quick-action"><span>◎</span> Discover properties</a>
            <a href="#favorites" class="quick-action"><span>♥</span> Review saved homes</a>
            <a href="#appointments" class="quick-action"><span>◷</span> Manage visits</a>
        </div>
    </section>

    <section id="favorites" class="dash-section">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Saved properties</h2>
                    <p class="muted">Homes you're keeping an eye on.</p>
                </div>
                <a href="{{ route('properties.index') }}" class="btn btn-outline btn-sm">Browse more</a>
            </div>

            @if ($favorites->count())
                <div class="property-grid property-grid-2">
                    @foreach ($favorites as $favorite)
                        @if ($favorite->property)
                            @include('partials.property-card', ['property' => $favorite->property])
                        @endif
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <h3>No saved properties yet</h3>
                    <p>Tap the heart icon on any listing to save it here.</p>
                    <a href="{{ route('properties.index') }}" class="btn btn-primary">Explore listings</a>
                </div>
            @endif
        </div>
    </section>

    <section id="appointments" class="dash-section">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>My visits</h2>
                    <p class="muted">Track requested, confirmed and completed site visits.</p>
                </div>
            </div>

            @if ($appointments->count())
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr><th>Property</th><th>Seller</th><th>When</th><th>Status</th><th class="text-right">Actions</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($appointments as $appointment)
                                <tr>
                                    <td>
                                        @if ($appointment->property)
                                            <a href="{{ route('properties.show', $appointment->property->slug) }}">{{ $appointment->property->title }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $appointment->seller?->name ?? '—' }}</td>
                                    <td>
                                        {{ $appointment->scheduled_date?->format('M d, Y') }}
                                        <span class="muted block">{{ $appointment->scheduled_time ?? 'Flexible' }}</span>
                                    </td>
                                    <td><span class="badge badge-appt-{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span></td>
                                    <td class="text-right">
                                        @if (in_array($appointment->status, ['requested', 'confirmed', 'rescheduled']))
                                            <form action="{{ route('buyer.appointments.cancel', $appointment) }}" method="POST" onsubmit="return confirm('Cancel this visit?')">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-danger btn-xs">Cancel</button>
                                            </form>
                                        @else
                                            <span class="muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <p>No visits booked yet. Open a property and request a site visit.</p>
                </div>
            @endif
        </div>
    </section>

    <section id="deals" class="dash-section">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Deals</h2>
                    <p class="muted">Follow negotiation and closing progress.</p>
                </div>
            </div>

            @if ($deals->count())
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr><th>Property</th><th>Seller</th><th>Stage</th><th>Agreed price</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($deals as $deal)
                                <tr>
                                    <td>{{ $deal->property?->title ?? '—' }}</td>
                                    <td>{{ $deal->seller?->name ?? '—' }}</td>
                                    <td><span class="badge badge-stage-{{ $deal->stage }}">{{ ucfirst(str_replace('_', ' ', $deal->stage)) }}</span></td>
                                    <td>{{ $deal->agreed_price ? '৳' . number_format((float) $deal->agreed_price) : '—' }}</td>
                                    <td><span class="badge badge-status-{{ $deal->status === 'closed' ? 'published' : 'draft' }}">{{ ucfirst($deal->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state"><p>No deals in progress.</p></div>
            @endif
        </div>
    </section>

    <section id="recommended" class="dash-section">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Recommended for you</h2>
                    <p class="muted">Popular verified listings you may like.</p>
                </div>
            </div>
            <div class="property-grid">
                @foreach ($recommended as $property)
                    @include('partials.property-card', ['property' => $property])
                @endforeach
            </div>
        </div>
    </section>
@endsection
