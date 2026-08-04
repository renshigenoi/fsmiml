<?php

use App\Models\User;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (User $user, int $id): bool {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('dashboard', function (User $user): bool {
    return in_array($user->role, [UserRole::Administrator, UserRole::Coordinator], true);
});

Broadcast::channel('work-order.{workOrder}', function (User $user, WorkOrder $workOrder): bool {
    return $user->can('view', $workOrder);
});
