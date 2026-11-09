<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Area;
use App\Models\City;
use App\Models\PropertyType;
use App\Models\Amenity;
use App\Models\Contact;
use App\Models\Seller;
use App\Services\PropertyService;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    protected PropertyService $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    public function properties(Request $request)
    {
        $filters = $request->only([
            'purpose', 'area_id', 'city_id', 'property_type_id', 'bedrooms', 'bathrooms',
            'min_price', 'max_price', 'min_size', 'max_size', 'is_featured', 'search',
            'sort_field', 'sort_dir', 'per_page'
        ]);
        $filters['status'] = 'published';
        $filters['verification_status'] = 'published';
        return response()->json($this->propertyService->list($filters));
    }

    public function propertyDetail($slug)
    {
        $property = Property::with(['propertyType', 'area', 'city', 'images', 'amenities', 'seller.seller'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();
        $property->increment('views_count');
        $similar = Property::with(['propertyType', 'area', 'images'])
            ->where('property_type_id', $property->property_type_id)
            ->where('id', '!=', $property->id)
            ->where('status', 'published')
            ->take(4)
            ->get();
        return response()->json(['property' => $property, 'similar' => $similar]);
    }

    public function featuredProperties()
    {
        $properties = Property::with(['propertyType', 'area', 'images'])
            ->where('status', 'published')
            ->where('verification_status', 'published')
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();
        return response()->json($properties);
    }

    public function cities()
    {
        return response()->json(
            City::where('is_active', true)
                ->with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
                ->orderBy('name')
                ->get()
        );
    }

    public function areas()
    {
        return response()->json(Area::where('is_active', true)->orderBy('sort_order')->get());
    }

    public function stats()
    {
        return response()->json([
            'total_properties' => Property::where('status', 'published')->count(),
            'total_sellers' => Seller::count(),
            'total_areas' => Area::where('is_active', true)->count(),
            'total_deals' => \App\Models\Deal::where('stage', 'deal_closed')->count(),
        ]);
    }

    public function propertyTypes()
    {
        return response()->json(PropertyType::where('is_active', true)->get());
    }

    public function amenities()
    {
        return response()->json(Amenity::where('is_active', true)->get());
    }

    public function contact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'required|string',
        ]);
        $contact = Contact::create($validated);
        return response()->json(['message' => 'Message sent successfully.'], 201);
    }
}
