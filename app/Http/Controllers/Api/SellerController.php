<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Deal;
use App\Models\Appointment;
use App\Services\PropertyService;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    protected PropertyService $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    public function dashboard()
    {
        $user = auth()->user();
        return response()->json([
            'total_properties' => Property::where('seller_id', $user->id)->count(),
            'published' => Property::where('seller_id', $user->id)->where('status', 'published')->count(),
            'pending' => Property::where('seller_id', $user->id)->where('verification_status', 'pending')->count(),
            'active_deals' => Deal::where('seller_id', $user->id)->where('status', 'active')->count(),
            'upcoming_appointments' => Appointment::where('seller_id', $user->id)->where('status', 'confirmed')->count(),
        ]);
    }

    public function properties(Request $request)
    {
        $filters = $request->only(['status', 'verification_status', 'per_page', 'sort_field', 'sort_dir']);
        $filters['seller_id'] = auth()->id();
        return response()->json($this->propertyService->list($filters));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'video_url' => 'nullable|string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);
        $validated['seller_id'] = auth()->id();
        $validated['status'] = 'draft';
        $validated['verification_status'] = 'pending';
        return response()->json($this->propertyService->create($validated), 201);
    }

    public function deals()
    {
        return response()->json(
            Deal::with(['property', 'buyer'])
                ->where('seller_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function appointments()
    {
        return response()->json(
            Appointment::with(['property', 'buyer'])
                ->where('seller_id', auth()->id())
                ->orderBy('scheduled_date', 'desc')
                ->get()
        );
    }
}
