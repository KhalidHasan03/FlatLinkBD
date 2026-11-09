<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'phone', 'preferred_areas', 'budget_min', 'budget_max', 'property_type_preference', 'notes'];
    protected $casts = ['budget_min' => 'decimal:2', 'budget_max' => 'decimal:2'];
    public function user() { return $this->belongsTo(User::class); }
}
