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
        'start_date',
        'end_date',
        'report_content',
        'summary',
        'files',
        'generated_by_type',
        'generated_by_id',
        'noted_by_name',
        'prepared_by_name'
    ];

    protected $casts = [
        'report_content' => 'array',
        'summary' => 'array',
        'files' => 'array',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function generatedBy()
    {
        if ($this->generated_by_type === 'admin') {
            return $this->belongsTo(Admin::class, 'generated_by_id');
        } elseif ($this->generated_by_type === 'deputy') {
            return $this->belongsTo(Deputy::class, 'generated_by_id');
        } elseif ($this->generated_by_type === 'head') {
            return $this->belongsTo(Head::class, 'generated_by_id');
        }
        return null;
    }
}
