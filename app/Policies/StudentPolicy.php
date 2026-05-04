<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function create(User $user): bool
    {
        // Only Office Staff and System Admin can register students
        return $user->isOfficeStaff() || $user->isSystemAdmin();
    }
}