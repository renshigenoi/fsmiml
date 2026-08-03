<?php

namespace App\Modules\Notification\Jobs;

use App\Modules\Notification\Enums\NotificationStatus;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Services\NotificationDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class DeliverNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;

    public int $tries;

    public function __construct(public readonly Notification $notification)
    {
        $this->queue = (string) config('notifications.queue', 'notifications');
        $this->tries = (int) config('notifications.retry.tries', 3);
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return config('notifications.retry.backoff', [10, 60, 300]);
    }

    public function handle(NotificationDeliveryService $delivery): void
    {
        $notification = Notification::query()
            ->with([
                'workOrder.customer',
                'workOrder.assignments.technician.user',
                'user.deviceTokens',
            ])
            ->find($this->notification->getKey());

        if ($notification === null || $notification->status !== NotificationStatus::Queued) {
            return;
        }

        $delivery->deliver($notification);
    }

    public function failed(?Throwable $exception = null): void
    {
        $notification = Notification::query()->find($this->notification->getKey());

        if ($notification === null) {
            return;
        }

        app(NotificationDeliveryService::class)->markFailed($notification, $exception?->getMessage() ?? 'Delivery failed.');
    }
}
