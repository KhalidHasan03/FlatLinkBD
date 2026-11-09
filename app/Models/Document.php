<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;
    protected $fillable = ['deal_id', 'property_id', 'name', 'file_path', 'type', 'uploaded_by', 'notes'];
    public function deal() { return $this->belongsTo(Deal::class); }
    public function property() { return $this->belongsTo(Property::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
}
