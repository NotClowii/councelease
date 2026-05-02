<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $primaryKey = 'report_id';

    protected $fillable = [
        'report_title',
        'report_type',
        'generated_by',
        'date_from',
        'date_to',
        'filters_applied',
        'file_path',
        'status',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'filters_applied' => 'array',
    ];

    public function generatedByUser()
    {
        return $this->belongsTo(User::class, 'generated_by', 'user_id');
    }
}
