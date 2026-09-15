<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Assignment\Enums\AssignmentStatus;
use App\Modules\Assignment\Models\Assignment;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;

class AssignmentPolicy
{
    public function respond(User $user, Assignment $assignment): bool
    {
        return $user->technician?->getKey() === $assignment->technician_id
            && $assignment->status === AssignmentStatus::Pending
            && $assignment->workOrder?->status === WorkOrderStatus::WaitingAcceptance;
    }
}
