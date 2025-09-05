<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'type',
        'period',
        'report_content',
        'summary',
        'files'
    ];

    protected $casts = [
        'report_content' => 'array',
        'summary' => 'array',
        'files' => 'array'
    ];
}
