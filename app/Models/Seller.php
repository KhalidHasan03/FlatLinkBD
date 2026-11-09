<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'business_name', 'phone', 'alternate_phone', 'address', 'nid_number', 'notes'];

    public function user() { return $this->belongsTo(User::class); }

    public function properties() { return $this->hasMany(Property::class, 'seller_id', 'user_id'); }
}
