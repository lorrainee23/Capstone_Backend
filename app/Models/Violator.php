<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Violator extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'violator_user_id',
        'email',
        'password',
        'email_verified',
        'first_name',
        'middle_name',
        'last_name',
        'mobile_number',
        'gender',
        'license_number',
        'plate_number',
        'model',
        'id_photo',
        'address'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'gender' => 'boolean',
        'password' => 'hashed'
    ];

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

    public function user()
    {
        return $this->belongsTo(User::class, 'violator_user_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'violators_id');
    }

    public function pendingTransactions()
    {
        return $this->transactions()->where('status', 'Pending');
    }

    public function paidTransactions()
    {
        return $this->transactions()->where('status', 'Paid');
    }

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