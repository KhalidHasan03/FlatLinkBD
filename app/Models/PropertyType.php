<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyType extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug', 'for_sale', 'for_rent', 'is_active'];
    protected $casts = ['for_sale' => 'boolean', 'for_rent' => 'boolean', 'is_active' => 'boolean'];
    public function properties() { return $this->hasMany(Property::class); }
}
