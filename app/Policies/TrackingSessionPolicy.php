<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\Tracking\Models\TrackingSession;

class TrackingSessionPolicy
{
    public function submitLocation(User $user, TrackingSession $trackingSession): bool
    {
        return $user->technician?->getKey() === $trackingSession->assignment->technician_id;
    }

    public function issueToken(User $user, TrackingSession $trackingSession): bool
    {
        return in_array($user->role, [UserRole::Administrator, UserRole::Coordinator], true);
    }
}
