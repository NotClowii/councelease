<?php

namespace App\Policies;

use App\Models\Session;
use App\Models\User;

class SessionPolicy
{
    public function complete(User $user, Session $session): bool
    {
        // Only the assigned counselor can mark a session complete
        return $user->isCounselor() &&
               $session->counselor_id === $user->counselor?->counselor_id;
    }
}