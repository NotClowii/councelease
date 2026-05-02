<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'student_id';

    protected $fillable = [
        'user_id',
        'student_num',
        'course',
        'year_level',
        'section',
        'department_id',
        'birthdate',
        'gender',
        'address',
        'guardian_name',
        'guardian_contact',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'year_level' => 'integer',
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
        return $this->hasMany(Appointment::class, 'student_id', 'student_id');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class, 'student_id', 'student_id');
    }

    public function pendingAppointments()
    {
        return $this->appointments()->whereIn('appointment_status', ['pending', 'approved']);
    }

    // Helper
    public function getYearLevelLabelAttribute(): string
    {
        $labels = [1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year', 5 => '5th Year'];
        return $labels[$this->year_level] ?? "{$this->year_level}th Year";
    }
}
