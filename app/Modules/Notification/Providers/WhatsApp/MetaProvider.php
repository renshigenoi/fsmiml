<?php

namespace App\Modules\Notification\Providers\WhatsApp;

use App\Modules\Notification\Data\NotificationContent;
use App\Modules\Notification\Data\ProviderResult;
use App\Modules\Notification\Exceptions\NotificationDeliveryException;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Providers\Contracts\NotificationProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * WhatsApp Business Cloud API (Meta).
 */
final class MetaProvider implements NotificationProvider
{
    public function send(Notification $notification, NotificationContent $content): ProviderResult
    {
        $token = config('notifications.whatsapp.meta.token');
        $phoneNumberId = config('notifications.whatsapp.meta.phone_number_id');

        if (blank($token) || blank($phoneNumberId)) {
            throw new NotificationDeliveryException('Meta WhatsApp is not configured: set META_WHATSAPP_TOKEN and META_WHATSAPP_PHONE_NUMBER_ID.');
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->post("https://graph.facebook.com/v21.0/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $notification->recipient,
                'type' => 'text',
                'text' => [
                    'body' => $content->body,
                ],
            ]);

        if ($response->failed()) {
            throw new NotificationDeliveryException('Meta WhatsApp send failed: '.Str::limit($response->body(), 500));
        }

        $id = $response->json('messages.0.id');

        return new ProviderResult(is_string($id) ? $id : null);
    }
}
