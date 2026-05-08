<?php
namespace App\Policies;
use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function create(User $user): bool
    {
        // Students and office staff can book appointments
        return $user->isStudent() || $user->isOfficeStaff();
    }

    public function approve(User $user, Appointment $appointment): bool
    {
        // Counselors, office staff, and admins can approve
        return $user->isCounselor() || $user->isOfficeStaff() || $user->isSystemAdmin();
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        if ($user->isStudent()) {
            return $appointment->student_id === $user->student?->student_id;
        }
        if ($user->isCounselor()) {
            return $appointment->counselor_id === $user->counselor?->counselor_id;
        }
        // Office staff and admins can cancel any appointment
        if ($user->isOfficeStaff() || $user->isSystemAdmin()) {
            return true;
        }
        return false;
    }

    public function startSession(User $user, Appointment $appointment): bool
    {
        return $user->isCounselor() &&
               $appointment->counselor_id === $user->counselor?->counselor_id;
    }
}