<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\City;
use App\Models\Contact;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use App\Services\PropertyService;
use Illuminate\Http\Request;

class PublicPageController extends Controller
{
    public function __construct(protected PropertyService $propertyService)
    {
    }

    public function home()
    {
        $stats = [
            'properties' => Property::where('status', 'published')->count(),
            'sellers' => User::where('role', 'seller')->count(),
            'cities' => City::where('is_active', true)->count(),
            'closed_deals' => \App\Models\Deal::where('stage', 'deal_closed')->count(),
        ];

        $featured = Property::with(['propertyType', 'area', 'city', 'images'])
            ->where('status', 'published')
            ->where('is_featured', true)
            ->orderByDesc('views_count')
            ->take(6)
            ->get();

        if ($featured->count() < 6) {
            $featured = $featured->merge(
                Property::with(['propertyType', 'area', 'city', 'images'])
                    ->where('status', 'published')
                    ->whereNotIn('id', $featured->pluck('id'))
                    ->orderByDesc('views_count')
                    ->take(6 - $featured->count())
                    ->get()
            );
        }

        $latest = Property::with(['propertyType', 'area', 'city', 'images'])
            ->where('status', 'published')
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        $cities = City::where('is_active', true)
            ->withCount(['areas' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();

        $propertyTypes = PropertyType::where('is_active', true)->orderBy('name')->get();

        return view('public.home', compact('stats', 'featured', 'latest', 'cities', 'propertyTypes'));
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'purpose', 'city_id', 'area_id', 'property_type_id', 'bedrooms', 'bathrooms',
            'min_price', 'max_price', 'min_size', 'max_size', 'search', 'sort_field', 'sort_dir', 'per_page',
        ]);

        $filters['status'] = 'published';
        $filters['verification_status'] = 'published';
        $filters['per_page'] = $filters['per_page'] ?? 9;

        $properties = $this->propertyService->list($filters);
        $properties->withQueryString();

        $cities = City::where('is_active', true)->with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])->orderBy('name')->get();
        $propertyTypes = PropertyType::where('is_active', true)->orderBy('name')->get();
        $areas = Area::where('is_active', true)->orderBy('sort_order')->get();

        return view('public.properties', compact('properties', 'cities', 'propertyTypes', 'areas', 'filters'));
    }

    public function show(Request $request, string $slug)
    {
        $property = Property::with(['propertyType', 'area', 'city', 'images', 'amenities', 'seller.seller'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $property->increment('views_count');

        $similar = Property::with(['propertyType', 'area', 'city', 'images'])
            ->where('status', 'published')
            ->where('id', '!=', $property->id)
            ->where(function ($q) use ($property) {
                $q->where('property_type_id', $property->property_type_id)
                    ->orWhere('city_id', $property->city_id);
            })
            ->orderByDesc('is_featured')
            ->take(3)
            ->get();

        $isFavorite = $request->user()
            ? $request->user()->favorites()->where('property_id', $property->id)->exists()
            : false;

        return view('public.property', compact('property', 'similar', 'isFavorite'));
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:5000',
        ]);

        Contact::create($validated);

        return redirect()->route('contact')->with('success', 'Thanks! Your message has been received and our team will reach out shortly.');
    }
}
