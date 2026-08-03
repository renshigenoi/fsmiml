<?php

namespace App\Modules\WorkOrder\Services;

use App\Models\User;
use App\Modules\Assignment\Enums\AssignmentStatus;
use App\Modules\Assignment\Models\Assignment;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\Tracking\Enums\TrackingSessionStatus;
use App\Modules\Tracking\Enums\TrackingTokenStatus;
use App\Modules\Tracking\Models\TrackingSession;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;
use App\Modules\WorkOrder\Events\WorkOrderStatusChanged;
use App\Modules\WorkOrder\Exceptions\InvalidWorkOrderTransition;
use App\Modules\WorkOrder\Models\WorkOrder;
use App\Modules\WorkOrder\Models\WorkOrderStatusHistory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class WorkOrderTransitionService
{
    /**
     * @var array<string, list<WorkOrderStatus>>
     */
    private const TRANSITIONS = [
        'accepted' => [WorkOrderStatus::OnTheWay, WorkOrderStatus::Cancelled],
        'arrived' => [WorkOrderStatus::Installation, WorkOrderStatus::Failed, WorkOrderStatus::Cancelled],
        'draft' => [],
        'failed' => [],
        'finished' => [],
        'installation' => [WorkOrderStatus::Finished, WorkOrderStatus::Failed, WorkOrderStatus::Cancelled],
        'on_the_way' => [WorkOrderStatus::Arrived, WorkOrderStatus::Cancelled],
        'rejected' => [],
        'waiting_acceptance' => [WorkOrderStatus::Cancelled],
        'cancelled' => [],
    ];

    public static function canTransition(WorkOrderStatus $from, WorkOrderStatus $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from->value], true);
    }

    public function transition(
        WorkOrder $workOrder,
        WorkOrderStatus $toStatus,
        User $actor,
        ?string $reason = null,
    ): WorkOrder {
        $result = DB::transaction(function () use ($workOrder, $toStatus, $actor, $reason): array {
            $lockedWorkOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->getKey());
            $fromStatus = $lockedWorkOrder->status;

            if (! self::canTransition($fromStatus, $toStatus)) {
                throw new InvalidWorkOrderTransition("Transition from {$fromStatus->value} to {$toStatus->value} is not allowed.");
            }

            $this->validateReason($toStatus, $reason);
            $assignment = $this->authorizeTransition($lockedWorkOrder, $fromStatus, $toStatus, $actor);

            $lockedWorkOrder->status = $toStatus;

            if ($toStatus === WorkOrderStatus::Cancelled) {
                $lockedWorkOrder->cancelled_reason = $reason;
            }

            if ($toStatus === WorkOrderStatus::Failed) {
                $lockedWorkOrder->failed_reason = $reason;
            }

            $lockedWorkOrder->save();

            $this->applyTrackingLifecycle($lockedWorkOrder, $assignment, $toStatus);
            $this->recordHistory($lockedWorkOrder, $fromStatus, $toStatus, $actor, $reason);

            return [$lockedWorkOrder, $fromStatus];
        });

        [$updatedWorkOrder, $fromStatus] = $result;

        WorkOrderStatusChanged::dispatch($updatedWorkOrder, $fromStatus, $toStatus, $actor);

        return $updatedWorkOrder;
    }

    private function authorizeTransition(
        WorkOrder $workOrder,
        WorkOrderStatus $fromStatus,
        WorkOrderStatus $toStatus,
        User $actor,
    ): ?Assignment {
        if ($toStatus === WorkOrderStatus::Cancelled) {
            $this->ensureCoordinator($actor);

            return null;
        }

        if ($toStatus === WorkOrderStatus::Failed && $this->isCoordinator($actor)) {
            return $this->activeAssignment($workOrder);
        }

        $assignment = $this->activeAssignmentFor($workOrder, $actor);

        if ($fromStatus === WorkOrderStatus::Accepted && $toStatus === WorkOrderStatus::OnTheWay) {
            return $assignment;
        }

        if (in_array($fromStatus, [WorkOrderStatus::OnTheWay, WorkOrderStatus::Arrived, WorkOrderStatus::Installation], true)) {
            return $assignment;
        }

        throw new InvalidWorkOrderTransition('This transition requires a supported Work Order action.');
    }

    private function activeAssignmentFor(WorkOrder $workOrder, User $actor): Assignment
    {
        $technician = $actor->technician;

        if ($technician === null) {
            throw new AuthorizationException('Only the assigned technician may perform this action.');
        }

        $assignment = Assignment::query()
            ->where('work_order_id', $workOrder->getKey())
            ->where('technician_id', $technician->getKey())
            ->where('status', AssignmentStatus::Accepted->value)
            ->lockForUpdate()
            ->latest('assigned_at')
            ->first();

        if ($assignment === null) {
            throw new AuthorizationException('The technician does not own an active assignment for this Work Order.');
        }

        return $assignment;
    }

    private function activeAssignment(WorkOrder $workOrder): ?Assignment
    {
        return Assignment::query()
            ->where('work_order_id', $workOrder->getKey())
            ->where('status', AssignmentStatus::Accepted->value)
            ->lockForUpdate()
            ->latest('assigned_at')
            ->first();
    }

    private function applyTrackingLifecycle(
        WorkOrder $workOrder,
        ?Assignment $assignment,
        WorkOrderStatus $toStatus,
    ): void {
        if ($toStatus === WorkOrderStatus::OnTheWay) {
            $session = TrackingSession::query()
                ->where('work_order_id', $workOrder->getKey())
                ->where('assignment_id', $assignment?->getKey())
                ->where('status', TrackingSessionStatus::Pending->value)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($session === null) {
                throw new InvalidWorkOrderTransition('A pending tracking session is required before starting a trip.');
            }

            $session->update([
                'status' => TrackingSessionStatus::Active,
                'started_at' => now(),
            ]);

            return;
        }

        if ($toStatus === WorkOrderStatus::Arrived) {
            $this->closeActiveSessions($workOrder, TrackingSessionStatus::Closed, 'arrived');

            return;
        }

        if (in_array($toStatus, [WorkOrderStatus::Finished, WorkOrderStatus::Cancelled, WorkOrderStatus::Failed], true)) {
            $sessionStatus = $toStatus === WorkOrderStatus::Cancelled
                ? TrackingSessionStatus::Cancelled
                : TrackingSessionStatus::Closed;

            $this->closeOpenSessions($workOrder, $sessionStatus, $toStatus->value);

            TrackingSession::query()
                ->where('work_order_id', $workOrder->getKey())
                ->each(function (TrackingSession $session): void {
                    $session->tokens()
                        ->where('status', TrackingTokenStatus::Active->value)
                        ->update([
                            'status' => TrackingTokenStatus::Revoked,
                            'revoked_at' => now(),
                        ]);
                });
        }
    }

    private function closeActiveSessions(WorkOrder $workOrder, TrackingSessionStatus $status, string $reason): void
    {
        TrackingSession::query()
            ->where('work_order_id', $workOrder->getKey())
            ->where('status', TrackingSessionStatus::Active->value)
            ->update([
                'status' => $status,
                'ended_at' => now(),
                'closed_reason' => $reason,
            ]);
    }

    private function closeOpenSessions(WorkOrder $workOrder, TrackingSessionStatus $status, string $reason): void
    {
        TrackingSession::query()
            ->where('work_order_id', $workOrder->getKey())
            ->whereIn('status', [TrackingSessionStatus::Pending->value, TrackingSessionStatus::Active->value])
            ->update([
                'status' => $status,
                'ended_at' => now(),
                'closed_reason' => $reason,
            ]);
    }

    private function recordHistory(
        WorkOrder $workOrder,
        WorkOrderStatus $fromStatus,
        WorkOrderStatus $toStatus,
        User $actor,
        ?string $reason,
    ): void {
        WorkOrderStatusHistory::query()->create([
            'work_order_id' => $workOrder->getKey(),
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'actor_user_id' => $actor->getKey(),
            'reason' => $reason,
            'occurred_at' => now(),
        ]);
    }

    private function validateReason(WorkOrderStatus $toStatus, ?string $reason): void
    {
        if (in_array($toStatus, [WorkOrderStatus::Cancelled, WorkOrderStatus::Failed], true) && blank($reason)) {
            throw new InvalidWorkOrderTransition("A reason is required when changing a Work Order to {$toStatus->value}.");
        }
    }

    private function ensureCoordinator(User $actor): void
    {
        if (! $this->isCoordinator($actor)) {
            throw new AuthorizationException('Only an administrator or coordinator may cancel a Work Order.');
        }
    }

    private function isCoordinator(User $actor): bool
    {
        return in_array($actor->role, [UserRole::Administrator, UserRole::Coordinator], true);
    }
}
