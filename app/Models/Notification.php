<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'sender_role',
        'target_role',
        'title',
        'message',
        'type',
    ];

    /**
     * Get the sender of this notification.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Scope to filter by target role.
     */
    public function scopeForRole($query, $role)
    {
        return $query->where('target_role', $role);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get formatted created date.
     */
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('M d, Y h:i A');
    }

    /**
     * Check if notification is an alert.
     */
    public function isAlert()
    {
        return $this->type === 'alert';
    }

    /**
     * Check if notification is a warning.
     */
    public function isWarning()
    {
        return $this->type === 'warning';
    }

    /**
     * Check if notification is a reminder.
     */
    public function isReminder()
    {
        return $this->type === 'reminder';
    }

    /**
     * Check if notification is info.
     */
    public function isInfo()
    {
        return $this->type === 'info';
    }
} 