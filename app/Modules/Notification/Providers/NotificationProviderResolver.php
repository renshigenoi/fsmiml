<?php

namespace App\Modules\Notification\Providers;

use App\Modules\Notification\Enums\NotificationChannel;
use App\Modules\Notification\Providers\Contracts\NotificationProvider;
use App\Modules\Notification\Providers\WhatsApp\WhatsAppProviderManager;

final class NotificationProviderResolver
{
    public function __construct(private readonly WhatsAppProviderManager $whatsapp) {}

    public function resolve(NotificationChannel $channel): NotificationProvider
    {
        return match ($channel) {
            NotificationChannel::Push => $this->pushProvider(),
            NotificationChannel::WhatsApp => $this->whatsapp->driver(),
            NotificationChannel::Email => new EmailNotificationProvider,
        };
    }

    private function pushProvider(): NotificationProvider
    {
        return match (config('notifications.channels.push.driver', 'log')) {
            'fcm' => new FcmProvider,
            default => new LogNotificationProvider,
        };
    }
}
