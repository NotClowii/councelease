<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'session_id';

    protected $fillable = [
        'appointment_id',
        'student_id',
        'counselor_id',
        'session_datetime',
        'actual_duration_minutes',
        'session_status',
        'session_type',
        'session_summary',
        'ended_at',
    ];

    protected $casts = [
        'session_datetime' => 'datetime',
        'ended_at' => 'datetime',
        'actual_duration_minutes' => 'integer',
    ];

    // Relationships
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function counselor()
    {
        return $this->belongsTo(Counselor::class, 'counselor_id', 'counselor_id');
    }

    public function caseNotes()
    {
        return $this->hasMany(CaseNote::class, 'session_id', 'session_id');
    }

    // Helpers
    public function isOngoing(): bool   { return $this->session_status === 'ongoing'; }
    public function isCompleted(): bool { return $this->session_status === 'completed'; }
}
