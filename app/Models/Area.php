<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug', 'city_id', 'is_active', 'sort_order'];
    protected $casts = ['is_active' => 'boolean'];
    public function city() { return $this->belongsTo(City::class); }
    public function properties() { return $this->hasMany(Property::class); }
}
