<?php

namespace App\Modules\Assignment\Services;

use App\Models\User;
use App\Modules\Assignment\Enums\AssignmentStatus;
use App\Modules\Assignment\Events\AssignmentCreated;
use App\Modules\Assignment\Events\AssignmentResponded;
use App\Modules\Assignment\Events\AssignmentSuperseded;
use App\Modules\Assignment\Exceptions\InvalidAssignment;
use App\Modules\Assignment\Models\Assignment;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\Identity\Models\Technician;
use App\Modules\Tracking\Enums\TrackingSessionStatus;
use App\Modules\Tracking\Models\TrackingSession;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;
use App\Modules\WorkOrder\Events\WorkOrderStatusChanged;
use App\Modules\WorkOrder\Models\WorkOrder;
use App\Modules\WorkOrder\Models\WorkOrderStatusHistory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssignmentService
{
    /**
     * @param  array<int, int>  $technicianIds
     * @return Collection<int, Assignment>
     */
    public function assignMany(WorkOrder $workOrder, array $technicianIds, User $actor): Collection
    {
        $this->ensureCoordinator($actor);

        $technicianIds = array_values(array_unique(array_map('intval', $technicianIds)));

        if ($technicianIds === []) {
            throw new InvalidAssignment('At least one technician is required.');
        }

        $result = DB::transaction(function () use ($workOrder, $technicianIds, $actor): array {
            $lockedWorkOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->getKey());

            if (! in_array($lockedWorkOrder->status, [WorkOrderStatus::Draft, WorkOrderStatus::Rejected], true)) {
                throw new InvalidAssignment('A Work Order can only be assigned from draft or rejected status.');
            }

            $technicians = Technician::query()
                ->whereIn('id', $technicianIds)
                ->where('is_active', true)
                ->lockForUpdate()
                ->get();

            if ($technicians->count() !== count($technicianIds)) {
                throw new InvalidAssignment('One or more selected technicians are inactive or missing.');
            }

            foreach ($technicians as $technician) {
                if ($this->hasScheduleConflict($lockedWorkOrder, $technician)) {
                    throw new InvalidAssignment("The selected technician {$technician->employee_code} has a conflicting active assignment.");
                }
            }

            $fromStatus = $lockedWorkOrder->status;
            $assignments = new Collection;

            foreach ($technicians as $technician) {
                $assignment = Assignment::query()->create([
                    'work_order_id' => $lockedWorkOrder->getKey(),
                    'technician_id' => $technician->getKey(),
                    'status' => AssignmentStatus::Pending,
                    'assigned_by' => $actor->getKey(),
                    'assigned_at' => now(),
                ]);

                TrackingSession::query()->create([
                    'work_order_id' => $lockedWorkOrder->getKey(),
                    'assignment_id' => $assignment->getKey(),
                    'status' => TrackingSessionStatus::Pending,
                    'realtime_channel' => Str::random(32),
                ]);

                $assignments->push($assignment);
            }

            $lockedWorkOrder->update(['status' => WorkOrderStatus::WaitingAcceptance]);
            $this->recordHistory($lockedWorkOrder, $fromStatus, WorkOrderStatus::WaitingAcceptance, $actor);

            return [$assignments, $lockedWorkOrder, $fromStatus];
        });

        [$assignments, $updatedWorkOrder, $fromStatus] = $result;

        foreach ($assignments as $assignment) {
            AssignmentCreated::dispatch($assignment);
        }

        WorkOrderStatusChanged::dispatch($updatedWorkOrder, $fromStatus, WorkOrderStatus::WaitingAcceptance, $actor);

        return $assignments;
    }

    public function accept(Assignment $assignment, User $actor): Assignment
    {
        return $this->respond($assignment, $actor, AssignmentStatus::Accepted);
    }

    public function reject(Assignment $assignment, User $actor, string $reason): Assignment
    {
        if (blank($reason)) {
            throw new InvalidAssignment('A rejection reason is required.');
        }

        return $this->respond($assignment, $actor, AssignmentStatus::Rejected, $reason);
    }

    private function respond(Assignment $assignment, User $actor, AssignmentStatus $status, ?string $reason = null): Assignment
    {
        $result = DB::transaction(function () use ($assignment, $actor, $status, $reason): array {
            $lockedAssignment = Assignment::query()->lockForUpdate()->findOrFail($assignment->getKey());
            $lockedWorkOrder = WorkOrder::query()->lockForUpdate()->findOrFail($lockedAssignment->work_order_id);

            $this->ensureAssignmentOwner($lockedAssignment, $actor);

            if ($lockedAssignment->status !== AssignmentStatus::Pending || $lockedWorkOrder->status !== WorkOrderStatus::WaitingAcceptance) {
                throw new InvalidAssignment('This assignment can no longer be responded to.');
            }

            $lockedAssignment->update([
                'status' => $status,
                'responded_at' => now(),
                'rejected_reason' => $reason,
            ]);

            if ($status === AssignmentStatus::Rejected) {
                TrackingSession::query()
                    ->where('assignment_id', $lockedAssignment->getKey())
                    ->where('status', TrackingSessionStatus::Pending->value)
                    ->update([
                        'status' => TrackingSessionStatus::Cancelled,
                        'ended_at' => now(),
                        'closed_reason' => 'cancelled',
                    ]);
            }

            $targetStatus = $status === AssignmentStatus::Accepted
                ? WorkOrderStatus::Accepted
                : WorkOrderStatus::Rejected;

            $lockedWorkOrder->update(['status' => $targetStatus]);
            $this->recordHistory($lockedWorkOrder, WorkOrderStatus::WaitingAcceptance, $targetStatus, $actor, $reason);

            if ($status === AssignmentStatus::Accepted) {
                $this->supersedePendingSiblings($lockedAssignment, $lockedWorkOrder);
            }

            return [$lockedAssignment, $lockedWorkOrder, $targetStatus];
        });

        [$updatedAssignment, $updatedWorkOrder, $targetStatus] = $result;

        AssignmentResponded::dispatch($updatedAssignment, $status);
        WorkOrderStatusChanged::dispatch($updatedWorkOrder, WorkOrderStatus::WaitingAcceptance, $targetStatus, $actor);

        return $updatedAssignment;
    }

    private function supersedePendingSiblings(Assignment $assignment, WorkOrder $workOrder): void
    {
        $siblings = Assignment::query()
            ->where('work_order_id', $workOrder->getKey())
            ->where('status', AssignmentStatus::Pending->value)
            ->where('id', '!=', $assignment->getKey())
            ->get();

        foreach ($siblings as $sibling) {
            $sibling->update([
                'status' => AssignmentStatus::Superseded,
                'superseded_at' => now(),
            ]);

            TrackingSession::query()
                ->where('assignment_id', $sibling->getKey())
                ->where('status', TrackingSessionStatus::Pending->value)
                ->update([
                    'status' => TrackingSessionStatus::Cancelled,
                    'ended_at' => now(),
                    'closed_reason' => 'superseded',
                ]);

            AssignmentSuperseded::dispatch($sibling);
        }
    }

    private function hasScheduleConflict(WorkOrder $workOrder, Technician $technician): bool
    {
        if ($workOrder->scheduled_end_at === null) {
            return false;
        }

        return Assignment::query()
            ->where('technician_id', $technician->getKey())
            ->whereIn('status', [AssignmentStatus::Pending->value, AssignmentStatus::Accepted->value])
            ->whereHas('workOrder', function ($query) use ($workOrder): void {
                $query->where('scheduled_start_at', '<', $workOrder->scheduled_end_at)
                    ->whereRaw('COALESCE(scheduled_end_at, scheduled_start_at) > ?', [$workOrder->scheduled_start_at]);
            })
            ->exists();
    }

    private function ensureAssignmentOwner(Assignment $assignment, User $actor): void
    {
        if ($actor->technician?->getKey() !== $assignment->technician_id) {
            throw new AuthorizationException('Only the assigned technician may respond to this assignment.');
        }
    }

    private function ensureCoordinator(User $actor): void
    {
        if (! in_array($actor->role, [UserRole::Administrator, UserRole::Coordinator], true)) {
            throw new AuthorizationException('Only an administrator or coordinator may assign a Work Order.');
        }
    }

    private function recordHistory(
        WorkOrder $workOrder,
        WorkOrderStatus $fromStatus,
        WorkOrderStatus $toStatus,
        User $actor,
        ?string $reason = null,
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
}
