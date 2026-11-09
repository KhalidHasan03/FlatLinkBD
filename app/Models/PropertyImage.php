<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PropertyImage extends Model
{
    use HasFactory;
    protected $fillable = ['property_id', 'image_path', 'sort_order', 'is_featured'];
    protected $casts = ['is_featured' => 'boolean'];
    protected $appends = ['url'];

    public function property() { return $this->belongsTo(Property::class); }

    public function getUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        if (preg_match('#^https?://#i', $this->image_path)) {
            return $this->image_path;
        }

        return Storage::disk('public')->url($this->image_path);
    }
}
