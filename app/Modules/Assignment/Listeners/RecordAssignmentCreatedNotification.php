<?php

namespace App\Modules\Assignment\Listeners;

use App\Modules\Assignment\Events\AssignmentCreated;
use App\Modules\Notification\Enums\NotificationChannel;
use App\Modules\Notification\Services\NotificationAuditService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class RecordAssignmentCreatedNotification implements ShouldQueue
{
    public function __construct(private readonly NotificationAuditService $notifications) {}

    public function handle(AssignmentCreated $event): void
    {
        $assignment = $event->assignment->loadMissing(['technician.user', 'workOrder']);
        $technician = $assignment->technician;

        // A7: jangan fatal di queue bila teknisi tak punya akun (mis. user
        // ter-soft-delete sebelum fix ini).
        if ($technician === null || $technician->user === null) {
            Log::warning('AssignmentCreated: teknisi tanpa user aktif, notifikasi dilewati.', [
                'assignment_id' => $assignment->getKey(),
                'technician_id' => $technician?->getKey(),
            ]);

            return;
        }

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
