<?php

namespace App\Modules\Notification\Providers\WhatsApp;

use App\Modules\Notification\Data\NotificationContent;
use App\Modules\Notification\Data\ProviderResult;
use App\Modules\Notification\Exceptions\NotificationDeliveryException;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Providers\Contracts\NotificationProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class WablasProvider implements NotificationProvider
{
    public function send(Notification $notification, NotificationContent $content): ProviderResult
    {
        $token = config('notifications.whatsapp.wablas.token');
        $domain = config('notifications.whatsapp.wablas.domain');

        if (blank($token) || blank($domain)) {
            throw new NotificationDeliveryException('Wablas is not configured: set WABLAS_TOKEN and WABLAS_DOMAIN.');
        }

        $response = Http::asJson()->post(rtrim((string) $domain, '/').'/api/send-message', [
            'phone' => $notification->recipient,
            'message' => $content->body,
            'token' => $token,
        ]);

        if ($response->failed()) {
            throw new NotificationDeliveryException('Wablas send failed: '.Str::limit($response->body(), 500));
        }

        $id = $response->json('data.id');

        return new ProviderResult(is_string($id) ? $id : null);
    }
}
