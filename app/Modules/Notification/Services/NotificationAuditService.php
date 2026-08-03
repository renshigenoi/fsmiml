<?php

namespace App\Modules\Notification\Services;

use App\Models\User;
use App\Modules\Notification\Enums\NotificationChannel;
use App\Modules\Notification\Enums\NotificationStatus;
use App\Modules\Notification\Models\Notification;
use App\Modules\WorkOrder\Models\WorkOrder;

class NotificationAuditService
{
    public function queue(
        ?User $user,
        ?WorkOrder $workOrder,
        NotificationChannel $channel,
        string $type,
        string $recipient,
        array $content = [],
    ): Notification {
        return Notification::query()->create([
            'user_id' => $user?->getKey(),
            'work_order_id' => $workOrder?->getKey(),
            'channel' => $channel,
            'type' => $type,
            'recipient' => $recipient,
            'content' => $content === [] ? null : $content,
            'status' => NotificationStatus::Queued,
        ]);
    }
}
