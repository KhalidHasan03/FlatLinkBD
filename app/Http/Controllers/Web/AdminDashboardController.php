<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Document;
use App\Models\Property;
use App\Models\User;
use App\Services\CommissionService;
use App\Services\DealService;
use App\Services\NotificationService;
use App\Services\PropertyService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected PropertyService $propertyService,
        protected DealService $dealService,
        protected CommissionService $commissionService,
        protected NotificationService $notificationService
    ) {
    }

    public function index()
    {
        $stats = [
            'total_properties' => Property::count(),
            'pending_verification' => Property::where('verification_status', 'pending')->count(),
            'published_properties' => Property::where('status', 'published')->count(),
            'total_deals' => Deal::count(),
            'active_deals' => Deal::where('status', 'active')->count(),
            'closed_deals' => Deal::where('stage', 'deal_closed')->count(),
            'total_sellers' => User::where('role', 'seller')->count(),
            'total_buyers' => User::where('role', 'buyer')->count(),
            'pending_appointments' => Appointment::where('status', 'requested')->count(),
            'total_commission' => (float) Deal::where('stage', 'deal_closed')->sum('commission_amount'),
            'unread_messages' => Contact::where('is_read', false)->count(),
        ];

        $propertiesByStatus = [
            'draft' => Property::where('status', 'draft')->count(),
            'published' => Property::where('status', 'published')->count(),
            'archived' => Property::where('status', 'archived')->count(),
        ];

        $dealsByStage = [
            'inquiry' => Deal::where('stage', 'inquiry')->count(),
            'visit_done' => Deal::where('stage', 'visit_done')->count(),
            'negotiation' => Deal::where('stage', 'negotiation')->count(),
            'deal_closed' => Deal::where('stage', 'deal_closed')->count(),
            'cancelled' => Deal::where('stage', 'cancelled')->count(),
        ];

        $commissionSeries = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $commissionSeries[] = [
                'label' => $month->format('M'),
                'value' => (float) Deal::where('stage', 'deal_closed')
                    ->whereYear('close_date', $month->year)
                    ->whereMonth('close_date', $month->month)
                    ->sum('commission_amount'),
            ];
        }
        $maxCommission = max(1, collect($commissionSeries)->max('value'));

        $pendingProperties = Property::with(['propertyType', 'area', 'city', 'seller'])
            ->where('verification_status', 'pending')
            ->orderBy('created_at')
            ->take(10)
            ->get();

        $properties = Property::with(['propertyType', 'area', 'seller'])
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'properties_page');

        $sellers = User::with('seller')
            ->withCount('properties')
            ->where('role', 'seller')
            ->orderByDesc('created_at')
            ->paginate(8, ['*'], 'sellers_page');

        $buyers = User::with('buyer')
            ->where('role', 'buyer')
            ->orderByDesc('created_at')
            ->paginate(8, ['*'], 'buyers_page');

        $appointments = Appointment::with(['property', 'buyer', 'seller'])
            ->orderByDesc('scheduled_date')
            ->paginate(10, ['*'], 'appointments_page');

        $deals = Deal::with(['property', 'buyer', 'seller', 'documents'])
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'deals_page');

        $contacts = Contact::orderByDesc('created_at')->paginate(10, ['*'], 'contacts_page');

        $documents = Document::with(['uploader', 'deal', 'property'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $recentActivities = ActivityLog::with('user')->orderByDesc('created_at')->take(8)->get();

        return view('dashboard.admin', compact(
            'stats', 'propertiesByStatus', 'dealsByStage', 'commissionSeries', 'maxCommission',
            'pendingProperties', 'properties', 'sellers', 'buyers', 'appointments', 'deals',
            'contacts', 'documents', 'recentActivities'
        ));
    }

    public function verifyProperty(Request $request, Property $property)
    {
        $validated = $request->validate([
            'verification_status' => 'required|in:verified,rejected,published',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $this->propertyService->updateVerification($property, $validated['verification_status'], $validated['admin_notes'] ?? null);

        if ($property->seller_id) {
            $this->notificationService->sendInApp(
                $property->seller_id,
                'property_' . $validated['verification_status'],
                'Listing ' . ucfirst($validated['verification_status']),
                'Your listing "' . $property->title . '" has been ' . $validated['verification_status'] . '.'
            );
        }

        return back()->with('success', 'Listing marked as ' . $validated['verification_status'] . '.');
    }

    public function toggleFeatured(Property $property)
    {
        $property->update(['is_featured' => !$property->is_featured]);

        return back()->with('success', $property->is_featured ? 'Listing featured.' : 'Listing removed from featured.');
    }

    public function updateUserStatus(Request $request, User $user)
    {
        $validated = $request->validate(['status' => 'required|in:active,inactive,banned']);
        $user->update($validated);

        return back()->with('success', 'User status updated to ' . $validated['status'] . '.');
    }

    public function updateAppointment(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'required|in:requested,confirmed,completed,cancelled,rescheduled',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $appointment->update($validated);

        $this->notificationService->sendInApp(
            $appointment->buyer_id,
            'appointment_' . $validated['status'],
            'Visit ' . ucfirst($validated['status']),
            'Your visit request for ' . optional($appointment->property)->title . ' is now ' . $validated['status'] . '.'
        );

        return back()->with('success', 'Appointment updated.');
    }

    public function updateDealStage(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'stage' => 'required|in:inquiry,visit_done,negotiation,deal_closed,cancelled',
            'agreed_price' => 'nullable|numeric|min:0',
            'commission_type' => 'nullable|in:percentage,fixed',
            'commission_value' => 'nullable|numeric|min:0',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $this->dealService->updateStage($deal, $validated['stage'], $validated);

        return back()->with('success', 'Deal moved to ' . str_replace('_', ' ', $validated['stage']) . '.');
    }

    public function recordCommission(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'commission_type' => 'required|in:percentage,fixed',
            'commission_value' => 'required|numeric|min:0',
        ]);

        $this->commissionService->record($deal, $validated['commission_type'], (float) $validated['commission_value']);

        return back()->with('success', 'Commission recorded.');
    }

    public function markContactRead(Contact $contact)
    {
        $contact->update(['is_read' => true]);

        return back()->with('success', 'Message marked as read.');
    }

    public function destroyContact(Contact $contact)
    {
        $contact->delete();

        return back()->with('success', 'Message deleted.');
    }

    public function storeDocument(Request $request)
    {
        $validated = $request->validate([
            'deal_id' => 'nullable|exists:deals,id',
            'property_id' => 'nullable|exists:properties,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:agreement,deed,id,other',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'notes' => 'nullable|string|max:2000',
        ]);

        $path = $request->file('file')->store('documents', 'public');
        $validated['file_path'] = $path;
        $validated['uploaded_by'] = $request->user()->id;
        unset($validated['file']);

        Document::create($validated);

        return back()->with('success', 'Document uploaded.');
    }

    public function destroyDocument(Document $document)
    {
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }

    public function destroyProperty(Property $property)
    {
        $property->delete();

        return back()->with('success', 'Listing deleted.');
    }
}
