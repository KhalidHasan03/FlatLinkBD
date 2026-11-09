<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Http\Request;

class AdminPropertyController extends Controller
{
    protected PropertyService $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'status', 'verification_status', 'area_id', 'city_id',
            'property_type_id', 'bedrooms', 'bathrooms', 'min_price', 'max_price',
            'min_size', 'max_size', 'seller_id', 'search', 'sort_field', 'sort_dir', 'per_page'
        ]);
        return response()->json($this->propertyService->list($filters));
    }

    public function pending()
    {
        $properties = Property::with(['propertyType', 'area', 'city', 'seller'])
            ->where('verification_status', 'pending')
            ->where('status', 'draft')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return response()->json($properties);
    }

    public function verify(Request $request, Property $property)
    {
        $validated = $request->validate([
            'verification_status' => 'required|in:verified,rejected,published',
            'admin_notes' => 'nullable|string',
        ]);
        return response()->json(
            $this->propertyService->updateVerification($property, $validated['verification_status'], $validated['admin_notes'] ?? null)
        );
    }

    public function toggleFeatured(Property $property)
    {
        $property->update(['is_featured' => !$property->is_featured]);
        return response()->json($property->fresh());
    }
}
