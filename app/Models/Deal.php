<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'property_id', 'buyer_id', 'seller_id', 'stage', 'agreed_price',
        'commission_type', 'commission_value', 'commission_amount',
        'deal_date', 'close_date', 'notes', 'admin_notes', 'status'
    ];
    protected $casts = [
        'agreed_price' => 'decimal:2',
        'commission_value' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'deal_date' => 'date',
        'close_date' => 'date',
    ];
    public function property() { return $this->belongsTo(Property::class); }
    public function buyer() { return $this->belongsTo(User::class, 'buyer_id'); }
    public function seller() { return $this->belongsTo(User::class, 'seller_id'); }
    public function documents() { return $this->hasMany(Document::class); }
}
