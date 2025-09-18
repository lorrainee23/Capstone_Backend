<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes,HasFactory;

    protected $fillable = [
        'sender_id',
        'sender_role',
        'sender_name',
        'target_id',
        'target_type',
        'violator_id',
        'transaction_id',
        'title',
        'message',
        'type',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->morphTo('sender', 'sender_role', 'sender_id');
    }

    public function target()
    {
        return $this->morphTo('target', 'target_type', 'target_id');
    }

    public function violator()
    {
        return $this->belongsTo(Violator::class, 'violator_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
