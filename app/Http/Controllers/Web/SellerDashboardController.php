<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Appointment;
use App\Models\City;
use App\Models\Deal;
use App\Models\Property;
use App\Models\PropertyType;
use App\Services\NotificationService;
use App\Services\PropertyService;
use Illuminate\Http\Request;

class SellerDashboardController extends Controller
{
    public function __construct(
        protected PropertyService $propertyService,
        protected NotificationService $notificationService
    ) {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_properties' => Property::where('seller_id', $user->id)->count(),
            'published' => Property::where('seller_id', $user->id)->where('status', 'published')->count(),
            'pending' => Property::where('seller_id', $user->id)->where('verification_status', 'pending')->count(),
            'active_deals' => Deal::where('seller_id', $user->id)->where('status', 'active')->count(),
            'upcoming_appointments' => Appointment::where('seller_id', $user->id)->where('status', 'confirmed')->count(),
            'total_views' => (int) Property::where('seller_id', $user->id)->sum('views_count'),
        ];

        $properties = Property::with(['propertyType', 'area', 'city', 'images'])
            ->where('seller_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'properties_page');

        $appointments = Appointment::with(['property', 'buyer'])
            ->where('seller_id', $user->id)
            ->orderByDesc('scheduled_date')
            ->take(20)
            ->get();

        $deals = Deal::with(['property', 'buyer'])
            ->where('seller_id', $user->id)
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        $cities = City::where('is_active', true)->with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])->orderBy('name')->get();
        $propertyTypes = PropertyType::where('is_active', true)->orderBy('name')->get();
        $amenities = Amenity::where('is_active', true)->orderBy('name')->get();

        return view('dashboard.seller', compact('stats', 'properties', 'appointments', 'deals', 'cities', 'propertyTypes', 'amenities'));
    }

    public function storeProperty(Request $request)
    {
        $validated = $this->validateProperty($request);
        $amenities = $validated['amenities'] ?? [];
        unset($validated['amenities']);

        $validated['seller_id'] = $request->user()->id;
        $validated['status'] = 'draft';
        $validated['verification_status'] = 'pending';

        $property = $this->propertyService->create(array_merge($validated, ['amenities' => $amenities]));

        if ($request->hasFile('images')) {
            $this->storeImages($request, $property);
        }

        return redirect()->route('seller.dashboard')->with('success', 'Listing submitted. Our team will verify it shortly.');
    }

    public function updateProperty(Request $request, Property $property)
    {
        $this->authorizeOwner($property);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'purpose' => 'sometimes|in:sale,rental',
            'price' => 'sometimes|numeric|min:0',
            'size_sqft' => 'sometimes|numeric|min:0',
            'bedrooms' => 'sometimes|integer|min:0',
            'bathrooms' => 'sometimes|integer|min:0',
            'condition' => 'sometimes|in:under_construction,new,renovated,old',
            'address' => 'sometimes|string',
            'status' => 'sometimes|in:draft,archived',
        ]);

        $property->update($validated);

        return redirect()->route('seller.dashboard')->with('success', 'Listing updated.');
    }

    public function destroyProperty(Property $property)
    {
        $this->authorizeOwner($property);
        $property->delete();

        return redirect()->route('seller.dashboard')->with('success', 'Listing deleted.');
    }

    public function uploadImages(Request $request, Property $property)
    {
        $this->authorizeOwner($property);

        $request->validate([
            'images' => 'required|array|max:8',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $this->storeImages($request, $property);

        return redirect()->route('seller.dashboard')->with('success', 'Images uploaded.');
    }

    public function updateAppointment(Request $request, Appointment $appointment)
    {
        if ((int) $appointment->seller_id !== (int) $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:confirmed,completed,cancelled,rescheduled',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $appointment->update($validated);

        $this->notificationService->sendInApp(
            $appointment->buyer_id,
            'appointment_' . $validated['status'],
            'Visit ' . ucfirst($validated['status']),
            'Your visit request for ' . optional($appointment->property)->title . ' is now ' . $validated['status'] . '.'
        );

        return redirect()->route('seller.dashboard')->with('success', 'Appointment marked as ' . $validated['status'] . '.');
    }

    protected function validateProperty(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'property_type_id' => 'nullable|exists:property_types,id',
            'purpose' => 'required|in:sale,rental',
            'price' => 'required|numeric|min:0',
            'price_negotiable' => 'boolean',
            'size_sqft' => 'required|numeric|min:0',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'floor_no' => 'nullable|integer|min:0',
            'total_floors' => 'nullable|integer|min:0',
            'condition' => 'required|in:under_construction,new,renovated,old',
            'address' => 'required|string',
            'area_id' => 'nullable|exists:areas,id',
            'city_id' => 'nullable|exists:cities,id',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'video_url' => 'nullable|url|max:255',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
            'images' => 'nullable|array|max:8',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);
    }

    protected function storeImages(Request $request, Property $property): void
    {
        $start = (int) $property->images()->max('sort_order');

        foreach ($request->file('images', []) as $index => $image) {
            $path = $image->store('properties/' . $property->id, 'public');
            $property->images()->create([
                'image_path' => $path,
                'sort_order' => $start + $index + 1,
                'is_featured' => false,
            ]);
        }
    }

    protected function authorizeOwner(Property $property): void
    {
        if ((int) $property->seller_id !== (int) auth()->id() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }
    }
}
