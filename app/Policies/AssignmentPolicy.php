<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Assignment\Models\Assignment;

class AssignmentPolicy
{
    public function respond(User $user, Assignment $assignment): bool
    {
        return $user->technician?->getKey() === $assignment->technician_id;
    }
}
