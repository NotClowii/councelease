<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'appointment_id';

    protected $fillable = [
        'student_id',
        'counselor_id',
        'appointment_datetime',
        'duration_minutes',
        'appointment_status',
        'concern_type',
        'concern_description',
        'cancellation_reason',
        'cancelled_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'appointment_datetime' => 'datetime',
        'cancelled_at' => 'datetime',
        'approved_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    // Status constants
    const STATUS_PENDING     = 'pending';
    const STATUS_APPROVED    = 'approved';
    const STATUS_RESCHEDULED = 'rescheduled';
    const STATUS_CANCELLED   = 'cancelled';
    const STATUS_COMPLETED   = 'completed';
    const STATUS_NO_SHOW     = 'no_show';

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function counselor()
    {
        return $this->belongsTo(Counselor::class, 'counselor_id', 'counselor_id');
    }

    public function session()
    {
        return $this->hasOne(Session::class, 'appointment_id', 'appointment_id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    // Helpers
    public function isPending(): bool     { return $this->appointment_status === self::STATUS_PENDING; }
    public function isApproved(): bool    { return $this->appointment_status === self::STATUS_APPROVED; }
    public function isCancelled(): bool   { return $this->appointment_status === self::STATUS_CANCELLED; }
    public function isCompleted(): bool   { return $this->appointment_status === self::STATUS_COMPLETED; }
    public function canBeCancelled(): bool
    {
        return in_array($this->appointment_status, [self::STATUS_PENDING, self::STATUS_APPROVED])
            && $this->appointment_datetime->isFuture();
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->appointment_status) {
            'pending'     => 'badge-warning',
            'approved'    => 'badge-success',
            'cancelled'   => 'badge-danger',
            'completed'   => 'badge-info',
            'no_show'     => 'badge-secondary',
            'rescheduled' => 'badge-primary',
            default       => 'badge-secondary',
        };
    }
}
