<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Violation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'fine_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fine_amount' => 'decimal:2',
    ];

    /**
     * Legacy: direct relationship when a transaction had a single violation_id.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'violation_id');
    }

    /**
     * Many-to-many transactions that include this violation (new model).
     */
    public function transactionsMany()
    {
        return $this->belongsToMany(Transaction::class, 'transaction_violation')
            ->withTimestamps();
    }

    /**
     * Get pending transactions for this violation type.
     */
    public function pendingTransactions()
    {
        return $this->hasMany(Transaction::class, 'violation_id')->where('status', 'Pending');
    }

    /**
     * Get paid transactions for this violation type.
     */
    public function paidTransactions()
    {
        return $this->hasMany(Transaction::class, 'violation_id')->where('status', 'Paid');
    }

    /**
     * Get total revenue from this violation type.
     */
    public function getTotalRevenueAttribute()
    {
        return $this->paidTransactions()->sum('fine_amount');
    }

    /**
     * Get pending revenue from this violation type.
     */
    public function getPendingRevenueAttribute()
    {
        return $this->pendingTransactions()->sum('fine_amount');
    }

    /**
     * Get violation count.
     */
    public function getViolationCountAttribute()
    {
        return $this->transactions()->count();
    }
} 