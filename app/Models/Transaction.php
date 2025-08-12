<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'violators_id',
        'violations_id',
        'apprehending_officer',
        'status',
        'location',
        'date_time',
        'fine_amount',
        'receipt',
        'vehicle_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_time' => 'datetime',
        'fine_amount' => 'decimal:2',
    ];

    /**
     * Get the violator for this transaction.
     */
    public function violator()
    {
        return $this->belongsTo(Violator::class, 'violators_id');
    }

    /**
     * Get the violation type for this transaction.
     */
    public function violation()
    {
        return $this->belongsTo(Violation::class, 'violations_id');
    }

    /**
     * Get the apprehending officer for this transaction.
     */
    public function apprehendingOfficer()
    {
        return $this->belongsTo(User::class, 'apprehending_officer');
    }

    /**
     * Check if transaction is pending.
     */
    public function isPending()
    {
        return $this->status === 'Pending';
    }

    /**
     * Check if transaction is paid.
     */
    public function isPaid()
    {
        return $this->status === 'Paid';
    }

    /**
     * Mark transaction as paid.
     */
    public function markAsPaid()
    {
        $this->update(['status' => 'Paid']);
    }

    /**
     * Get formatted date time.
     */
    public function getFormattedDateTimeAttribute()
    {
        return $this->date_time->format('M d, Y h:i A');
    }

    /**
     * Get formatted fine amount.
     */
    public function getFormattedFineAmountAttribute()
    {
        return '₱' . number_format($this->fine_amount, 2);
    }
} 