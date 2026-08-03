<?php

namespace App\Modules\Notification\Providers\WhatsApp;

use App\Modules\Notification\Data\NotificationContent;
use App\Modules\Notification\Data\ProviderResult;
use App\Modules\Notification\Exceptions\NotificationDeliveryException;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Providers\Contracts\NotificationProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class FonnteProvider implements NotificationProvider
{
    public function send(Notification $notification, NotificationContent $content): ProviderResult
    {
        $token = config('notifications.whatsapp.fonnte.token');

        if (blank($token)) {
            throw new NotificationDeliveryException('Fonnte is not configured: set FONNTE_TOKEN.');
        }

        $response = Http::withHeaders(['Authorization' => $token])
            ->asForm()
            ->post('https://api.fonnte.com/send', [
                'target' => $notification->recipient,
                'message' => $content->body,
                'countryCode' => config('notifications.whatsapp.default_country_code', '62'),
            ]);

        if ($response->failed()) {
            throw new NotificationDeliveryException('Fonnte send failed: '.Str::limit($response->body(), 500));
        }

        $id = $response->json('id');

        return new ProviderResult(is_string($id) ? $id : null);
    }
}
