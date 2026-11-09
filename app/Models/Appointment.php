<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    protected $fillable = ['property_id', 'buyer_id', 'seller_id', 'scheduled_date', 'scheduled_time', 'status', 'admin_notes', 'buyer_notes'];
    protected $casts = ['scheduled_date' => 'date'];
    public function property() { return $this->belongsTo(Property::class); }
    public function buyer() { return $this->belongsTo(User::class, 'buyer_id'); }
    public function seller() { return $this->belongsTo(User::class, 'seller_id'); }
}
