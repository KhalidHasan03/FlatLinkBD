<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Property;

class FavoriteWebController extends Controller
{
    public function store(Property $property)
    {
        Favorite::firstOrCreate([
            'user_id' => auth()->id(),
            'property_id' => $property->id,
        ]);

        return back()->with('success', 'Saved to your favorites.');
    }

    public function destroy(Property $property)
    {
        Favorite::where('user_id', auth()->id())
            ->where('property_id', $property->id)
            ->delete();

        return back()->with('success', 'Removed from your favorites.');
    }
}
