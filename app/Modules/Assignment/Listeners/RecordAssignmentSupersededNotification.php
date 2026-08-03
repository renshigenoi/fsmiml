<?php

namespace App\Modules\Assignment\Listeners;

use App\Modules\Assignment\Events\AssignmentSuperseded;
use App\Modules\Notification\Enums\NotificationChannel;
use App\Modules\Notification\Services\NotificationAuditService;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecordAssignmentSupersededNotification implements ShouldQueue
{
    public function handle(AssignmentSuperseded $event, NotificationAuditService $notifications): void
    {
        $assignment = $event->assignment->loadMissing(['technician.user', 'workOrder']);
        $user = $assignment->technician?->user;

        if ($user === null) {
            return;
        }

        $notifications->queue(
            $user,
            $assignment->workOrder,
            NotificationChannel::Push,
            'assignment_superseded',
            $user->email,
        );
    }
}
