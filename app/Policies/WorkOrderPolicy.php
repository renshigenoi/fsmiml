<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\WorkOrder\Models\WorkOrder;

class WorkOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isCoordinator($user) || $user->technician !== null;
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        return $this->isCoordinator($user)
            || $workOrder->assignments()->where('technician_id', $user->technician?->getKey())->exists();
    }

    public function create(User $user): bool
    {
        return $this->isCoordinator($user);
    }

    public function assign(User $user, WorkOrder $workOrder): bool
    {
        return $this->isCoordinator($user);
    }

    public function cancel(User $user, WorkOrder $workOrder): bool
    {
        return $this->isCoordinator($user);
    }

    private function isCoordinator(User $user): bool
    {
        return in_array($user->role, [UserRole::Administrator, UserRole::Coordinator], true);
    }
}
