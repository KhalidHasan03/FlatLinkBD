<?php
namespace App\Services;

use App\Models\Property;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropertyService
{
    public function list(array $filters = [], $perPage = 12)
    {
        $query = Property::with(['propertyType', 'area', 'city', 'images', 'amenities', 'seller.seller']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['verification_status'])) {
            $query->where('verification_status', $filters['verification_status']);
        }
        if (!empty($filters['purpose'])) {
            $query->where('purpose', $filters['purpose']);
        }
        if (!empty($filters['area_id'])) {
            $query->where('area_id', $filters['area_id']);
        }
        if (!empty($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }
        if (!empty($filters['property_type_id'])) {
            $query->where('property_type_id', $filters['property_type_id']);
        }
        if (!empty($filters['bedrooms'])) {
            $query->where('bedrooms', '>=', (int) $filters['bedrooms']);
        }
        if (!empty($filters['bathrooms'])) {
            $query->where('bathrooms', '>=', (int) $filters['bathrooms']);
        }
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        if (!empty($filters['min_size'])) {
            $query->where('size_sqft', '>=', $filters['min_size']);
        }
        if (!empty($filters['max_size'])) {
            $query->where('size_sqft', '<=', $filters['max_size']);
        }
        if (!empty($filters['seller_id'])) {
            $query->where('seller_id', $filters['seller_id']);
        }
        if (!empty($filters['is_featured'])) {
            $query->where('is_featured', (bool) $filters['is_featured']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $allowedSorts = ['created_at', 'price', 'size_sqft', 'bedrooms', 'views_count', 'is_featured'];
        $sortField = in_array($filters['sort_field'] ?? null, $allowedSorts, true)
            ? $filters['sort_field']
            : 'created_at';
        $sortDir = strtolower($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDir)->orderBy('id', 'desc');

        $perPage = (int) ($filters['per_page'] ?? $perPage);
        $perPage = max(1, min($perPage, 60));

        return $query->paginate($perPage);
    }

    public function create(array $data): Property
    {
        $amenities = $data['amenities'] ?? null;
        unset($data['amenities']);

        $property = Property::create($data);
        if (!empty($amenities)) {
            $property->amenities()->sync($amenities);
        }
        ActivityLog::create([
            'user_id' => auth()->id(),
            'loggable_type' => Property::class,
            'loggable_id' => $property->id,
            'action' => 'created',
            'description' => 'Property created: ' . $property->title,
        ]);
        return $property->load(['propertyType', 'area', 'city', 'images', 'amenities', 'seller']);
    }

    public function updateVerification(Property $property, string $status, ?string $notes = null): Property
    {
        $property->update([
            'verification_status' => $status,
            'admin_notes' => $notes ?? $property->admin_notes,
            'status' => $status === 'published' ? 'published' : $property->status,
        ]);
        ActivityLog::create([
            'user_id' => auth()->id(),
            'loggable_type' => Property::class,
            'loggable_id' => $property->id,
            'action' => 'verification_' . $status,
            'description' => "Property verification status changed to {$status}",
        ]);
        return $property->fresh()->load(['propertyType', 'area', 'city', 'images', 'amenities', 'seller']);
    }
}
