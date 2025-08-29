<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'violators_id',
        'first_name',
        'middle_name',
        'last_name',
        'plate_number',
        'make',
        'model',
        'barangay',
        'city',
        'province',
        'vehicle_type',
    ];

    /**
     * Vehicle belongs to a Violator (driver who was caught)
     */
    public function violator()
    {
        return $this->belongsTo(Violator::class);
    }

    /**
     * Vehicle may be part of many Transactions (violations recorded)
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get full name of the vehicle owner
     */
    public function ownerName(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }
}
