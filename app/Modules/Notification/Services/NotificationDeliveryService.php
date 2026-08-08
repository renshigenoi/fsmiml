<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Enums\NotificationStatus;
use App\Modules\Notification\Enums\NotificationChannel;
use App\Modules\Notification\Exceptions\NotificationDeliveryException;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Providers\NotificationProviderResolver;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class NotificationDeliveryService
{
    public function __construct(
        private readonly NotificationProviderResolver $resolver,
        private readonly NotificationContentBuilder $builder,
    ) {}

    public function deliver(Notification $notification): void
    {
        if ($notification->status !== NotificationStatus::Queued) {
            return;
        }

        // FCM tanpa device token terdaftar → tandai gagal sekali (tanpa retry &
        // tanpa error log penuh). Berlaku di semua jalur pengiriman.
        if (
            $notification->channel === NotificationChannel::Push->value
            && ($notification->user === null || $notification->user->deviceTokens->isEmpty())
        ) {
            $this->markFailed($notification, 'No registered device token for the recipient user.');
            Log::warning('FCM push dilewati: tidak ada device token terdaftar.', [
                'notification_id' => $notification->getKey(),
                'user_id' => $notification->user_id,
            ]);

            return;
        }

        $content = $this->builder->build($notification);

        try {
            $result = $this->resolver->resolve($notification->channel)->send($notification, $content);
        } catch (NotificationDeliveryException $exception) {
            throw $exception;
        }

        $notification->update([
            'status' => NotificationStatus::Sent,
            'provider_message_id' => $result->messageId,
            'sent_at' => now(),
            'failure_reason' => null,
        ]);
    }

    public function markFailed(Notification $notification, string $reason): void
    {
        if ($notification->status === NotificationStatus::Sent) {
            return;
        }

        $notification->update([
            'status' => NotificationStatus::Failed,
            'failure_reason' => Str::limit($reason, 1000),
        ]);
    }
}
