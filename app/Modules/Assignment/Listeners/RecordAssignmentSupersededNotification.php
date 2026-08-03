<?php

namespace App\Modules\Assignment\Listeners;

use App\Modules\Assignment\Events\AssignmentSuperseded;
use App\Modules\Notification\Enums\NotificationChannel;
use App\Modules\Notification\Services\NotificationAuditService;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecordAssignmentSupersededNotification implements ShouldQueue
{
    public function __construct(private readonly NotificationAuditService $notifications) {}

    public function handle(AssignmentSuperseded $event): void
    {
        $assignment = $event->assignment->loadMissing(['technician.user', 'workOrder']);
        $user = $assignment->technician?->user;

        if ($user === null) {
            return;
        }

        $this->notifications->queue(
            $user,
            $assignment->workOrder,
            NotificationChannel::Push,
            'assignment_superseded',
            $user->email,
        );
    }
}
