<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Enums\NotificationStatus;
use App\Modules\Notification\Exceptions\NotificationDeliveryException;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Providers\NotificationProviderResolver;
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
