<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id', 'title', 'slug', 'description', 'property_type_id', 'purpose',
        'price', 'price_negotiable', 'size_sqft', 'bedrooms', 'bathrooms',
        'floor_no', 'total_floors', 'condition', 'address', 'area_id', 'city_id',
        'latitude', 'longitude', 'featured_image', 'video_url',
        'verification_status', 'admin_notes', 'status', 'views_count', 'is_featured'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_negotiable' => 'boolean',
        'size_sqft' => 'decimal:2',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
    ];

    protected $appends = ['cover_image']; 

    protected static function booted()
    {
        static::creating(function ($property) {
            if (empty($property->slug)) {
                $property->slug = Str::slug($property->title) . '-' . uniqid();
            }
        });
    }

    public function seller() { return $this->belongsTo(User::class, 'seller_id'); }
    public function propertyType() { return $this->belongsTo(PropertyType::class); }
    public function area() { return $this->belongsTo(Area::class); }
    public function city() { return $this->belongsTo(City::class); }
    public function images() { return $this->hasMany(PropertyImage::class)->orderBy('sort_order'); }
    public function amenities() { return $this->belongsToMany(Amenity::class, 'property_amenities'); }
    public function appointments() { return $this->hasMany(Appointment::class); }
    public function deals() { return $this->hasMany(Deal::class); }
    public function documents() { return $this->hasMany(Document::class); }
    public function favorites() { return $this->hasMany(Favorite::class); }
    public function favoritedBy() { return $this->belongsToMany(User::class, 'favorites')->withTimestamps(); }

    public function getCoverImageAttribute(): ?string
    {
        if (!empty($this->featured_image)) {
            if (preg_match('#^https?://#i', $this->featured_image)) {
                return $this->featured_image;
            }

            return Storage::disk('public')->url($this->featured_image);
        }

        $first = $this->relationLoaded('images')
            ? $this->images->first()
            : $this->images()->first();

        return $first?->url;
    }
}
