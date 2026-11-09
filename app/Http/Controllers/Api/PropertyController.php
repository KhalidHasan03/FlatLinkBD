<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    protected PropertyService $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'status', 'verification_status', 'purpose', 'area_id', 'city_id',
            'property_type_id', 'bedrooms', 'bathrooms', 'min_price', 'max_price',
            'min_size', 'max_size', 'seller_id', 'is_featured', 'search', 'sort_field', 'sort_dir', 'per_page'
        ]);

        $user = $request->user();

        if (!$user->isSuperAdmin() && !$user->isStaff()) {
            if ($user->isSeller()) {
                $filters['seller_id'] = $user->id;
            } else {
                $filters['status'] = 'published';
                $filters['verification_status'] = 'published';
                unset($filters['seller_id']);
            }
        }

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
        $property = $this->propertyService->create($validated);

        return response()->json($property, 201);
    }

    public function show(Request $request, Property $property)
    {
        $user = $request->user();

        if ($property->status !== 'published'
            && (int) $property->seller_id !== (int) $user->id
            && !$user->isSuperAdmin()
            && !$user->isStaff()) {
            return response()->json(['message' => 'Property not found.'], 404);
        }

        $property->load(['propertyType', 'area', 'city', 'images', 'amenities', 'seller.seller']);
        $property->increment('views_count');
        return response()->json($property);
    }

    public function update(Request $request, Property $property)
    {
        if ($property->seller_id !== auth()->id() && !auth()->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'property_type_id' => 'nullable|exists:property_types,id',
            'purpose' => 'sometimes|in:sale,rental',
            'price' => 'sometimes|numeric|min:0',
            'price_negotiable' => 'boolean',
            'size_sqft' => 'sometimes|numeric|min:0',
            'bedrooms' => 'sometimes|integer|min:0',
            'bathrooms' => 'sometimes|integer|min:0',
            'floor_no' => 'nullable|integer|min:0',
            'total_floors' => 'nullable|integer|min:0',
            'condition' => 'sometimes|in:under_construction,new,renovated,old',
            'address' => 'sometimes|string',
            'area_id' => 'nullable|exists:areas,id',
            'city_id' => 'nullable|exists:cities,id',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'featured_image' => 'nullable|string',
            'video_url' => 'nullable|string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        $amenities = $validated['amenities'] ?? null;
        unset($validated['amenities']);

        $property->update($validated);
        if ($amenities !== null) {
            $property->amenities()->sync($amenities);
        }

        return response()->json($property->fresh()->load(['propertyType', 'area', 'city', 'images', 'amenities', 'seller']));
    }

    public function destroy(Property $property)
    {
        if ($property->seller_id !== auth()->id() && !auth()->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        $property->delete();
        return response()->json(['message' => 'Property deleted.']);
    }

    public function uploadImages(Request $request, Property $property)
    {
        if ((int) $property->seller_id !== (int) auth()->id() && !auth()->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $request->validate(['images' => 'required|array', 'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120']);
        $uploaded = [];
        foreach ($request->file('images') as $image) {
            $path = $image->store('properties/' . $property->id, 'public');
            $img = $property->images()->create(['image_path' => $path, 'sort_order' => 0]);
            $uploaded[] = $img;
        }
        return response()->json($uploaded, 201);
    }
}
