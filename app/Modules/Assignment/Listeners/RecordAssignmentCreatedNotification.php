<?php

namespace App\Modules\Assignment\Listeners;

use App\Modules\Assignment\Events\AssignmentCreated;
use App\Modules\Notification\Enums\NotificationChannel;
use App\Modules\Notification\Services\NotificationAuditService;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecordAssignmentCreatedNotification implements ShouldQueue
{
    public function handle(AssignmentCreated $event, NotificationAuditService $notifications): void
    {
        $assignment = $event->assignment->loadMissing(['technician.user', 'workOrder']);
        $recipient = $assignment->technician->user->email;

        $notifications->queue(
            $assignment->technician->user,
            $assignment->workOrder,
            NotificationChannel::Push,
            'assignment_created',
            $recipient,
        );
    }
}
