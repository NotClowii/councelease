<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Counselor extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'counselor_id';

    protected $fillable = [
        'user_id',
        'department_id',
        'specialization',
        'license_number',
        'max_appointments_per_day',
        'available_days',
        'available_from',
        'available_until',
    ];

    protected $casts = [
        'available_days' => 'array',
        'max_appointments_per_day' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'counselor_id', 'counselor_id');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class, 'counselor_id', 'counselor_id');
    }

    public function caseNotes()
    {
        return $this->hasMany(CaseNote::class, 'counselor_id', 'counselor_id');
    }

    // Check if counselor is available at given datetime
    public function isAvailableAt(\Carbon\Carbon $dateTime): bool
    {
        $dayOfWeek = $dateTime->dayOfWeek;
        $availableDays = $this->available_days ?? [1, 2, 3, 4, 5];

        if (!in_array($dayOfWeek, $availableDays)) return false;

        $time = $dateTime->format('H:i:s');
        if ($time < $this->available_from || $time > $this->available_until) return false;

        // Check for existing approved appointment at same time
        $exists = $this->appointments()
            ->where('appointment_datetime', $dateTime)
            ->whereIn('appointment_status', ['approved', 'pending'])
            ->exists();

        return !$exists;
    }

    // Count today's appointments
    public function todayAppointmentsCount(): int
    {
        return $this->appointments()
            ->whereDate('appointment_datetime', today())
            ->whereIn('appointment_status', ['approved', 'pending'])
            ->count();
    }
}
