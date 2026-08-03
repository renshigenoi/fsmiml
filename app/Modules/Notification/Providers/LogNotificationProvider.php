<?php

namespace App\Modules\Notification\Providers;

use App\Modules\Notification\Data\NotificationContent;
use App\Modules\Notification\Data\ProviderResult;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Providers\Contracts\NotificationProvider;
use Illuminate\Support\Facades\Log;

/**
 * Local-development driver: writes the composed message to the log instead of
 * calling an external provider. Audits are marked as sent so the pipeline can
 * be exercised end-to-end without real credentials.
 */
final class LogNotificationProvider implements NotificationProvider
{
    public function send(Notification $notification, NotificationContent $content): ProviderResult
    {
        Log::info('[FSM Notification] '.$notification->channel->value.' -> '.$notification->recipient, [
            'type' => $notification->type,
            'title' => $content->title,
            'body' => $content->body,
            'tracking_url' => $content->trackingUrl,
            'work_order_id' => $notification->work_order_id,
        ]);

        return new ProviderResult;
    }
}
