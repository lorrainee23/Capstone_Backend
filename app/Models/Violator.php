<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Violator extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'id',
        'email',
        'password',
        'email_verified',
        'first_name',
        'middle_name',
        'last_name',
        'mobile_number',
        'gender',
        'license_number',
        'barangay',
        'city',
        'province',
        'professional',
        'id_photo',
        'license_suspended_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'gender' => 'boolean',
        'password' => 'hashed',
        'professional' => 'boolean',
        'license_suspended_at' => 'datetime',
    ];

    protected $appends = ['id_photo_url'];


    /** 🔹 Accessors */
    public function getFullNameAttribute()
    {
        $name = $this->first_name;
        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }
        $name .= ' ' . $this->last_name;
        return $name;
    }

    public function getGenderTextAttribute()
    {
        return $this->gender ? 'Male' : 'Female';
    }

    public function getFullAddressAttribute()
    {
        return "{$this->barangay}, {$this->city}, {$this->province}";
    }

    public function getIdPhotoUrlAttribute()
    {
        if (!$this->id_photo) {
            $configured = config('cloudinary.default_id_photo_url');
            return $configured ?: url('storage/id_photos/photo.png');
        }
        // If value already looks like a URL (Cloudinary), return as-is
        if (preg_match('/^https?:\/\//i', $this->id_photo)) {
            return $this->id_photo;
        }
        // Else assume local filename in storage
        return url('storage/id_photos/' . $this->id_photo);
    }

    /** 🔹 Relationships */

    public function pendingTransactions()
    {
        return $this->transactions()->where('status', 'Pending');
    }

    public function paidTransactions()
    {
        return $this->transactions()->where('status', 'Paid');
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'violator_id');
    }
    public function vehicles()
{
    return $this->hasMany(Vehicle::class, 'violators_id');
}

    /** 🔹 Helpers */
    public function isRepeatOffender()
    {
        return $this->transactions()->count() > 1;
    }

    public function getTotalFinesAttribute()
    {
        return $this->transactions()->sum('fine_amount');
    }

    public function getUnpaidFinesAttribute()
    {
        return $this->transactions()->where('status', 'Pending')->sum('fine_amount');
    }
}
