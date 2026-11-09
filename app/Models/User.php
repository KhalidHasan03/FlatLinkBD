<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $appends = ['avatar_url'];

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'avatar', 'role', 'status'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function seller() { return $this->hasOne(Seller::class); }
    public function buyer() { return $this->hasOne(Buyer::class); }
    public function properties() { return $this->hasMany(Property::class, 'seller_id'); }
    public function appointmentsAsBuyer() { return $this->hasMany(Appointment::class, 'buyer_id'); }
    public function appointmentsAsSeller() { return $this->hasMany(Appointment::class, 'seller_id'); }
    public function dealsAsBuyer() { return $this->hasMany(Deal::class, 'buyer_id'); }
    public function dealsAsSeller() { return $this->hasMany(Deal::class, 'seller_id'); }
    public function documents() { return $this->hasMany(Document::class, 'uploaded_by'); }
    public function activityLogs() { return $this->hasMany(ActivityLog::class); }
    public function favorites() { return $this->hasMany(Favorite::class); }
    public function favoriteProperties() { return $this->belongsToMany(Property::class, 'favorites')->withTimestamps(); }

    public function getAvatarUrlAttribute(): ?string
    {
        if (empty($this->avatar)) {
            return null;
        }

        if (preg_match('#^https?://#i', $this->avatar)) {
            return $this->avatar;
        }

        return Storage::disk('public')->url($this->avatar);
    }

    public function isSuperAdmin() { return $this->role === 'super_admin'; }
    public function isSeller() { return $this->role === 'seller'; }
    public function isBuyer() { return $this->role === 'buyer'; }
    public function isStaff() { return $this->role === 'staff'; }
}
