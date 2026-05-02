<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'note_id';
    protected $table = 'case_notes';

    protected $fillable = [
        'session_id',
        'counselor_id',
        'notes_content',
        'note_type',
        'is_confidential',
        'interventions_used',
        'follow_up_actions',
        'next_session_recommended',
    ];

    protected $casts = [
        'is_confidential' => 'boolean',
        'interventions_used' => 'array',
        'next_session_recommended' => 'date',
    ];

    // Relationships
    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'session_id');
    }

    public function counselor()
    {
        return $this->belongsTo(Counselor::class, 'counselor_id', 'counselor_id');
    }
}
