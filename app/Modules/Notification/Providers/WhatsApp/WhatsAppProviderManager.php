<?php

namespace App\Modules\Notification\Providers\WhatsApp;

use App\Modules\Notification\Providers\Contracts\NotificationProvider;
use App\Modules\Notification\Providers\LogNotificationProvider;

final class WhatsAppProviderManager
{
    public function driver(?string $driver = null): NotificationProvider
    {
        return match ($driver ?? config('notifications.channels.whatsapp.driver', 'log')) {
            'fonnte' => new FonnteProvider,
            'wablas' => new WablasProvider,
            'meta' => new MetaProvider,
            'gowa' => new GowaProvider,
            default => new LogNotificationProvider,
        };
    }
}
