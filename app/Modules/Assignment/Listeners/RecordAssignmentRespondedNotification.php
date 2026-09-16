<?php

namespace App\Modules\Assignment\Listeners;

use App\Modules\Assignment\Events\AssignmentResponded;
use App\Modules\Notification\Enums\NotificationChannel;
use App\Modules\Notification\Services\NotificationAuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class RecordAssignmentRespondedNotification implements ShouldQueue
{
    public function __construct(private readonly NotificationAuditService $notifications) {}

    public function handle(AssignmentResponded $event): void
    {
        $assignment = $event->assignment->loadMissing(['assignedBy', 'workOrder']);

        // A7: assignedBy bisa null (akun pengassign terhapus) — jangan fatal di queue.
        if ($assignment->assignedBy === null) {
            Log::warning('AssignmentResponded: penugasan tanpa assignedBy, notifikasi dilewati.', [
                'assignment_id' => $assignment->getKey(),
            ]);

            return;
        }

        $this->notifications->queue(
            $assignment->assignedBy,
            $assignment->workOrder,
            NotificationChannel::Push,
            "assignment_{$event->status->value}",
            $assignment->assignedBy->email,
        );
    }
}
