<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'violators_id',
        'owner_first_name',
        'owner_middle_name',
        'owner_last_name',
        'plate_number',
        'make',
        'model',
        'owner_barangay',
        'owner_city',
        'owner_province',
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
        return trim("{$this->owner_first_name} {$this->owner_middle_name} {$this->owner_last_name}");
    }
}
