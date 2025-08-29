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
        'ticket_number',
        'violator_id',
        'violation_id',
        'apprehending_officer',
        'vehicle_id',
        'status',
        'location',
        'date_time',
        'fine_amount',
        'receipt',
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
     * Get the violation type for this transaction.
     */
    public function violation()
    {
        return $this->belongsTo(Violation::class, 'violation_id');
    }

    /**
     * Get the apprehending officer for this transaction.
     */
    public function apprehendingOfficer()
    {
        return $this->belongsTo(User::class, 'apprehending_officer');
    }
    /**
     * Get the violator for this transaction.
     */
    public function violator()
    {
        return $this->belongsTo(Violator::class, 'violator_id');
    }
    /**
     * Get the vehcile for this transaction.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
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
    /**
     * Get formatted fine amount.
     */
    protected static function booted()
    {
        static::creating(function ($transaction) {
            if (empty($transaction->ticket_number)) {
                $last = Transaction::max('ticket_number') ?? 999;
                $transaction->ticket_number = $last + 1;
            }
        });
    }
}
