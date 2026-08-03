<?php

namespace App\Modules\Assignment\Listeners;

use App\Modules\Assignment\Events\AssignmentResponded;
use App\Modules\Notification\Enums\NotificationChannel;
use App\Modules\Notification\Services\NotificationAuditService;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecordAssignmentRespondedNotification implements ShouldQueue
{
    public function __construct(private readonly NotificationAuditService $notifications) {}

    public function handle(AssignmentResponded $event): void
    {
        $assignment = $event->assignment->loadMissing(['assignedBy', 'workOrder']);

        $this->notifications->queue(
            $assignment->assignedBy,
            $assignment->workOrder,
            NotificationChannel::Push,
            "assignment_{$event->status->value}",
            $assignment->assignedBy->email,
        );
    }
}
