<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function create(User $user): bool
    {
        // Only students can book appointments
        return $user->isStudent();
    }

    public function approve(User $user, Appointment $appointment): bool
    {
        // Only counselors can approve
        return $user->isCounselor();
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        // Student can cancel their own, counselor can cancel theirs
        if ($user->isStudent()) {
            return $appointment->student_id === $user->student?->student_id;
        }
        if ($user->isCounselor()) {
            return $appointment->counselor_id === $user->counselor?->counselor_id;
        }
        return false;
    }

    public function startSession(User $user, Appointment $appointment): bool
    {
        // Only the assigned counselor can start a session
        return $user->isCounselor() &&
               $appointment->counselor_id === $user->counselor?->counselor_id;
    }
}