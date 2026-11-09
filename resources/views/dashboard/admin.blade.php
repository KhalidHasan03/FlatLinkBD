@extends('layouts.dashboard')

@section('title', 'Admin Dashboard · Flat-LinkBD')
@section('dash_title', 'Operations control center')
@section('dash_subtitle', 'Verify listings, manage users, deals and messages')

@section('sidebar')
    <a href="#overview" class="dash-link is-active">Overview</a>
    <a href="#verification" class="dash-link">Verification queue</a>
    <a href="#properties" class="dash-link">Properties</a>
    <a href="#users" class="dash-link">Users</a>
    <a href="#appointments" class="dash-link">Appointments</a>
    <a href="#deals" class="dash-link">Deals</a>
    <a href="#messages" class="dash-link">Messages</a>
    <a href="#documents" class="dash-link">Documents</a>
    <a href="#activity" class="dash-link">Activity</a>
@endsection

@section('content')
    <section id="overview" class="dash-section">
        <div class="stat-grid">
            <div class="stat-card"><span>Total properties</span><strong>{{ number_format($stats['total_properties']) }}</strong></div>
            <div class="stat-card"><span>Pending verification</span><strong>{{ number_format($stats['pending_verification']) }}</strong></div>
            <div class="stat-card"><span>Published</span><strong>{{ number_format($stats['published_properties']) }}</strong></div>
            <div class="stat-card"><span>Active deals</span><strong>{{ number_format($stats['active_deals']) }}</strong></div>
            <div class="stat-card"><span>Closed deals</span><strong>{{ number_format($stats['closed_deals']) }}</strong></div>
            <div class="stat-card"><span>Sellers</span><strong>{{ number_format($stats['total_sellers']) }}</strong></div>
            <div class="stat-card"><span>Buyers</span><strong>{{ number_format($stats['total_buyers']) }}</strong></div>
            <div class="stat-card"><span>Pending visits</span><strong>{{ number_format($stats['pending_appointments']) }}</strong></div>
            <div class="stat-card stat-card-accent"><span>Commission earned</span><strong>৳{{ number_format($stats['total_commission']) }}</strong></div>
            <div class="stat-card"><span>Unread messages</span><strong>{{ number_format($stats['unread_messages']) }}</strong></div>
        </div>

        <div class="chart-grid">
            <div class="panel">
                <h2>Properties by status</h2>
                <div class="chart-bars">
                    @php $maxStatus = max(1, max($propertiesByStatus)); @endphp
                    @foreach ($propertiesByStatus as $label => $value)
                        <div class="chart-row">
                            <span class="chart-label">{{ ucfirst($label) }}</span>
                            <div class="chart-track"><span class="chart-fill" style="width: {{ round($value / $maxStatus * 100) }}%"></span></div>
                            <span class="chart-value">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="panel">
                <h2>Deals by stage</h2>
                <div class="chart-bars">
                    @php $maxStage = max(1, max($dealsByStage)); @endphp
                    @foreach ($dealsByStage as $label => $value)
                        <div class="chart-row">
                            <span class="chart-label">{{ ucfirst(str_replace('_', ' ', $label)) }}</span>
                            <div class="chart-track"><span class="chart-fill chart-fill-alt" style="width: {{ round($value / $maxStage * 100) }}%"></span></div>
                            <span class="chart-value">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="panel">
                <h2>Commission · last 6 months</h2>
                <div class="mini-bars">
                    @foreach ($commissionSeries as $point)
                        <div class="mini-bar">
                            <span class="mini-bar-value">৳{{ number_format($point['value'] / 1000, 0) }}k</span>
                            <div class="mini-bar-track">
                                <span style="height: {{ max(4, round($point['value'] / $maxCommission * 100)) }}%"></span>
                            </div>
                            <span class="mini-bar-label">{{ $point['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section id="verification" class="dash-section">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Verification queue</h2>
                    <p class="muted">{{ $pendingProperties->count() }} listing(s) awaiting review.</p>
                </div>
            </div>

            @if ($pendingProperties->count())
                <div class="queue-grid">
                    @foreach ($pendingProperties as $property)
                        <div class="queue-card">
                            <div class="queue-media">
                                @if ($property->cover_image)
                                    <img src="{{ $property->cover_image }}" alt="">
                                @endif
                            </div>
                            <div class="queue-body">
                                <h3>{{ $property->title }}</h3>
                                <p class="muted">{{ $property->area?->name ?? '—' }} · {{ $property->propertyType?->name ?? 'Property' }} · ৳{{ number_format((float) $property->price) }}</p>
                                <p class="muted small">By {{ $property->seller?->name ?? '—' }}</p>
                                <form action="{{ route('admin.properties.verify', $property) }}" method="POST" class="stack-form">
                                    @csrf
                                    <div class="field">
                                        <label>Decision</label>
                                        <select name="verification_status">
                                            <option value="published">Publish (verified)</option>
                                            <option value="verified">Verify (keep draft)</option>
                                            <option value="rejected">Reject</option>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>Notes</label>
                                        <textarea name="admin_notes" rows="2" placeholder="Optional feedback for the seller"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">Submit decision</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state"><p>Nothing awaiting verification. You're all caught up.</p></div>
            @endif
        </div>
    </section>

    <section id="properties" class="dash-section">
        <div class="panel">
            <div class="panel-head"><div><h2>All properties</h2><p class="muted">Verify, feature or remove any listing.</p></div></div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr><th>Property</th><th>Seller</th><th>Price</th><th>Status</th><th>Verification</th><th class="text-right">Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($properties as $property)
                            <tr>
                                <td>
                                    <div class="table-property">
                                        @if ($property->cover_image)
                                            <img src="{{ $property->cover_image }}" alt="" class="table-thumb">
                                        @endif
                                        <div>
                                            <strong>{{ Str::limit($property->title, 42) }}</strong>
                                            <span>{{ $property->area?->name ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $property->seller?->name ?? '—' }}</td>
                                <td>৳{{ number_format((float) $property->price) }}</td>
                                <td><span class="badge badge-status-{{ $property->status }}">{{ ucfirst($property->status) }}</span></td>
                                <td><span class="badge badge-verify-{{ $property->verification_status }}">{{ ucfirst($property->verification_status) }}</span></td>
                                <td class="text-right">
                                    <div class="row-actions">
                                        @if ($property->status === 'published')
                                            <a href="{{ route('properties.show', $property->slug) }}" class="btn btn-ghost btn-xs">View</a>
                                        @endif
                                        <form action="{{ route('admin.properties.featured', $property) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline btn-xs">{{ $property->is_featured ? 'Unfeature' : 'Feature' }}</button>
                                        </form>
                                        <form action="{{ route('admin.properties.destroy', $property) }}" method="POST" onsubmit="return confirm('Delete this property?')">
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
        </div>
    </section>

    <section id="users" class="dash-section">
        <div class="panel">
            <div class="panel-head"><div><h2>Sellers</h2><p class="muted">Manage seller accounts and access.</p></div></div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Seller</th><th>Business</th><th>Listings</th><th>Status</th><th class="text-right">Action</th></tr></thead>
                    <tbody>
                        @foreach ($sellers as $seller)
                            <tr>
                                <td><strong>{{ $seller->name }}</strong><span class="muted block">{{ $seller->email }}</span></td>
                                <td>{{ $seller->seller?->business_name ?? '—' }}</td>
                                <td>{{ $seller->properties_count }}</td>
                                <td><span class="badge badge-status-{{ $seller->status === 'active' ? 'published' : 'archived' }}">{{ ucfirst($seller->status) }}</span></td>
                                <td class="text-right">
                                    <form action="{{ route('admin.users.status', $seller) }}" method="POST" class="inline-flex-form">
                                        @csrf @method('PATCH')
                                        <select name="status">
                                            @foreach (['active', 'inactive', 'banned'] as $status)
                                                <option value="{{ $status }}" @selected($seller->status === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-outline btn-xs">Update</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $sellers->links() }}</div>
        </div>

        <div class="panel">
            <div class="panel-head"><div><h2>Buyers</h2><p class="muted">Manage buyer accounts and access.</p></div></div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Buyer</th><th>Phone</th><th>Budget</th><th>Status</th><th class="text-right">Action</th></tr></thead>
                    <tbody>
                        @foreach ($buyers as $buyer)
                            <tr>
                                <td><strong>{{ $buyer->name }}</strong><span class="muted block">{{ $buyer->email }}</span></td>
                                <td>{{ $buyer->buyer?->phone ?? $buyer->phone ?? '—' }}</td>
                                <td>{{ $buyer->buyer?->budget_max ? '৳' . number_format((float) $buyer->buyer->budget_max) : '—' }}</td>
                                <td><span class="badge badge-status-{{ $buyer->status === 'active' ? 'published' : 'archived' }}">{{ ucfirst($buyer->status) }}</span></td>
                                <td class="text-right">
                                    <form action="{{ route('admin.users.status', $buyer) }}" method="POST" class="inline-flex-form">
                                        @csrf @method('PATCH')
                                        <select name="status">
                                            @foreach (['active', 'inactive', 'banned'] as $status)
                                                <option value="{{ $status }}" @selected($buyer->status === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-outline btn-xs">Update</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $buyers->links() }}</div>
        </div>
    </section>

    <section id="appointments" class="dash-section">
        <div class="panel">
            <div class="panel-head"><div><h2>Appointments</h2><p class="muted">Oversee and update site visits across the platform.</p></div></div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Property</th><th>Buyer</th><th>Seller</th><th>When</th><th>Status</th><th class="text-right">Update</th></tr></thead>
                    <tbody>
                        @foreach ($appointments as $appointment)
                            <tr>
                                <td>{{ Str::limit($appointment->property?->title ?? '—', 34) }}</td>
                                <td>{{ $appointment->buyer?->name ?? '—' }}</td>
                                <td>{{ $appointment->seller?->name ?? '—' }}</td>
                                <td>{{ $appointment->scheduled_date?->format('M d, Y') }}<span class="muted block">{{ $appointment->scheduled_time ?? '—' }}</span></td>
                                <td><span class="badge badge-appt-{{ $appointment->status }}">{{ ucfirst($appointment->status) }}</span></td>
                                <td class="text-right">
                                    <form action="{{ route('admin.appointments.update', $appointment) }}" method="POST" class="inline-flex-form">
                                        @csrf @method('PATCH')
                                        <select name="status">
                                            @foreach (['requested', 'confirmed', 'completed', 'cancelled', 'rescheduled'] as $status)
                                                <option value="{{ $status }}" @selected($appointment->status === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-outline btn-xs">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $appointments->links() }}</div>
        </div>
    </section>

    <section id="deals" class="dash-section">
        <div class="panel">
            <div class="panel-head"><div><h2>Deals</h2><p class="muted">Move deals through the pipeline and record commission.</p></div></div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Property</th><th>Buyer</th><th>Stage</th><th>Agreed price</th><th>Commission</th><th class="text-right">Manage</th></tr></thead>
                    <tbody>
                        @foreach ($deals as $deal)
                            <tr>
                                <td>{{ Str::limit($deal->property?->title ?? '—', 30) }}</td>
                                <td>{{ $deal->buyer?->name ?? '—' }}</td>
                                <td><span class="badge badge-stage-{{ $deal->stage }}">{{ ucfirst(str_replace('_', ' ', $deal->stage)) }}</span></td>
                                <td>{{ $deal->agreed_price ? '৳' . number_format((float) $deal->agreed_price) : '—' }}</td>
                                <td>{{ $deal->commission_amount ? '৳' . number_format((float) $deal->commission_amount) : '—' }}</td>
                                <td class="text-right">
                                    <details class="inline-details">
                                        <summary class="btn btn-outline btn-xs">Manage</summary>
                                        <div class="inline-panel">
                                            <form action="{{ route('admin.deals.stage', $deal) }}" method="POST" class="stack-form">
                                                @csrf @method('PATCH')
                                                <div class="field">
                                                    <label>Stage</label>
                                                    <select name="stage">
                                                        @foreach (['inquiry', 'visit_done', 'negotiation', 'deal_closed', 'cancelled'] as $stage)
                                                            <option value="{{ $stage }}" @selected($deal->stage === $stage)>{{ ucfirst(str_replace('_', ' ', $stage)) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="field">
                                                    <label>Agreed price (৳)</label>
                                                    <input type="number" name="agreed_price" min="0" step="1000" value="{{ (float) $deal->agreed_price ?: '' }}">
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-sm">Update stage</button>
                                            </form>

                                            <form action="{{ route('admin.deals.commission', $deal) }}" method="POST" class="stack-form">
                                                @csrf
                                                <div class="field-row">
                                                    <div class="field">
                                                        <label>Type</label>
                                                        <select name="commission_type">
                                                            <option value="percentage" @selected($deal->commission_type === 'percentage')>Percentage</option>
                                                            <option value="fixed" @selected($deal->commission_type === 'fixed')>Fixed</option>
                                                        </select>
                                                    </div>
                                                    <div class="field">
                                                        <label>Value</label>
                                                        <input type="number" name="commission_value" min="0" step="0.01" value="{{ (float) $deal->commission_value ?: '' }}">
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-outline btn-sm">Record commission</button>
                                            </form>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $deals->links() }}</div>
        </div>
    </section>

    <section id="messages" class="dash-section">
        <div class="panel">
            <div class="panel-head"><div><h2>Messages</h2><p class="muted">Inbox from the public contact form.</p></div></div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>From</th><th>Message</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                        @foreach ($contacts as $contact)
                            <tr>
                                <td>
                                    <strong>{{ $contact->name }}</strong>
                                    <span class="muted block">{{ $contact->email }}</span>
                                    <span class="muted block">{{ $contact->phone }}</span>
                                </td>
                                <td>{{ Str::limit($contact->message, 90) }}</td>
                                <td><span class="badge {{ $contact->is_read ? 'badge-verified' : 'badge-gold' }}">{{ $contact->is_read ? 'Read' : 'New' }}</span></td>
                                <td class="text-right">
                                    <div class="row-actions">
                                        @unless ($contact->is_read)
                                            <form action="{{ route('admin.contacts.read', $contact) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-outline btn-xs">Mark read</button>
                                            </form>
                                        @endunless
                                        <form action="{{ route('admin.contacts.destroy', $contact) }}" method="POST" onsubmit="return confirm('Delete this message?')">
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
            <div class="pagination-wrap">{{ $contacts->links() }}</div>
        </div>
    </section>

    <section id="documents" class="dash-section">
        <div class="panel">
            <div class="panel-head"><div><h2>Documents</h2><p class="muted">Agreements, deeds and identity files.</p></div></div>

            <form action="{{ route('admin.documents.store') }}" method="POST" enctype="multipart/form-data" class="doc-upload">
                @csrf
                <div class="field"><label>Name</label><input type="text" name="name" required></div>
                <div class="field">
                    <label>Type</label>
                    <select name="type">
                        <option value="agreement">Agreement</option>
                        <option value="deed">Deed</option>
                        <option value="id">ID</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="field">
                    <label>Attach to deal</label>
                    <select name="deal_id">
                        <option value="">None</option>
                        @foreach ($deals as $deal)
                            <option value="{{ $deal->id }}">#{{ $deal->id }} · {{ Str::limit($deal->property?->title ?? 'Deal', 30) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>File</label><input type="file" name="file" required></div>
                <button type="submit" class="btn btn-primary btn-sm">Upload</button>
            </form>

            @if ($documents->count())
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>Name</th><th>Type</th><th>Linked</th><th>Uploaded by</th><th class="text-right">Actions</th></tr></thead>
                        <tbody>
                            @foreach ($documents as $document)
                                <tr>
                                    <td>{{ $document->name }}</td>
                                    <td>{{ ucfirst($document->type) }}</td>
                                    <td>{{ $document->deal?->property?->title ?? $document->property?->title ?? '—' }}</td>
                                    <td>{{ $document->uploader?->name ?? '—' }}</td>
                                    <td class="text-right">
                                        <form action="{{ route('admin.documents.destroy', $document) }}" method="POST" onsubmit="return confirm('Delete this document?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state"><p>No documents uploaded yet.</p></div>
            @endif
        </div>
    </section>

    <section id="activity" class="dash-section">
        <div class="panel">
            <div class="panel-head"><div><h2>Recent activity</h2><p class="muted">Latest actions across the platform.</p></div></div>
            <ul class="activity-list">
                @foreach ($recentActivities as $activity)
                    <li>
                        <span class="activity-dot"></span>
                        <div>
                            <strong>{{ ucfirst(str_replace('_', ' ', $activity->action)) }}</strong>
                            <p>{{ $activity->description }}</p>
                            <span class="muted small">{{ $activity->user?->name ?? 'System' }} · {{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endsection
