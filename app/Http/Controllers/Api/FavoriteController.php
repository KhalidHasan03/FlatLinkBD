<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Property;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = Favorite::with(['property.propertyType', 'property.area', 'property.images'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($favorites);
    }

    public function store(Property $property)
    {
        $favorite = Favorite::firstOrCreate([
            'user_id' => auth()->id(),
            'property_id' => $property->id,
        ]);

        return response()->json($favorite, 201);
    }

    public function destroy(Property $property)
    {
        Favorite::where('user_id', auth()->id())
            ->where('property_id', $property->id)
            ->delete();

        return response()->json(['message' => 'Removed from favorites.']);
    }
}
