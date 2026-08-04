<?php

namespace App\Modules\Assignment\Listeners;

use App\Modules\Assignment\Events\AssignmentCreated;
use App\Modules\Notification\Enums\NotificationChannel;
use App\Modules\Notification\Services\NotificationAuditService;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecordAssignmentCreatedNotification implements ShouldQueue
{
    public function __construct(private readonly NotificationAuditService $notifications) {}

    public function handle(AssignmentCreated $event): void
    {
        $assignment = $event->assignment->loadMissing(['technician.user', 'workOrder']);
        $technician = $assignment->technician;
        $recipient = $technician->user->email;

        $this->notifications->queue(
            $technician->user,
            $assignment->workOrder,
            NotificationChannel::Push,
            'assignment_created',
            $recipient,
        );

        if (filled($technician->phone)) {
            $this->notifications->queue(
                $technician->user,
                $assignment->workOrder,
                NotificationChannel::WhatsApp,
                'assignment_created',
                $technician->phone,
            );
        }
    }
}
