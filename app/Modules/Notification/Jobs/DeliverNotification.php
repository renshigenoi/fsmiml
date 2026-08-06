<?php

namespace App\Modules\Notification\Jobs;

use App\Modules\Notification\Enums\NotificationStatus;
use App\Modules\Notification\Enums\NotificationChannel;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Services\NotificationDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
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

        // FCM tanpa device token terdaftar → tandai gagal sekali (tanpa retry berulang) biar log tidak penuh error.
        if ($notification->channel === NotificationChannel::Push->value
            && ($notification->user === null || $notification->user->deviceTokens->isEmpty())) {
            app(NotificationDeliveryService::class)->markFailed(
                $notification,
                'No registered device token for the recipient user.',
            );
            Log::warning('FCM push dilewati: tidak ada device token terdaftar.', [
                'notification_id' => $notification->getKey(),
                'user_id' => $notification->user_id,
            ]);

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
