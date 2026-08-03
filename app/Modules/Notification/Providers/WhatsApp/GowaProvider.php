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
 * GoWA (Go WhatsApp Web Multi-Device) — self-hosted WhatsApp gateway.
 *
 * Endpoint: POST {base}/send/message with JSON {phone, message}.
 * GoWA expects Indonesian numbers in 62xxx format (not 08xxx).
 */
final class GowaProvider implements NotificationProvider
{
    public function send(Notification $notification, NotificationContent $content): ProviderResult
    {
        $baseUrl = rtrim((string) config('notifications.whatsapp.gowa.base_url'), '/');
        $apiKey = config('notifications.whatsapp.gowa.api_key');
        $basicUser = config('notifications.whatsapp.gowa.basic_user');
        $basicPass = config('notifications.whatsapp.gowa.basic_pass');

        if (blank($baseUrl) || (blank($apiKey) && (blank($basicUser) || blank($basicPass)))) {
            throw new NotificationDeliveryException(
                'GOWA is not configured: set GOWA_BASE_URL and either GOWA_API_KEY or GOWA_BASIC_USER/GOWA_BASIC_PASS.'
            );
        }

        $headers = filled($basicUser) && filled($basicPass)
            ? ['Authorization' => 'Basic '.base64_encode($basicUser.':'.$basicPass)]
            : ['Authorization' => 'Bearer '.$apiKey];

        // GoWA v8+: device-scoped calls require X-Device-Id when more than one
        // device is registered. Falls back to the default device when blank.
        $deviceId = config('notifications.whatsapp.gowa.device_id');

        if (filled($deviceId)) {
            $headers['X-Device-Id'] = (string) $deviceId;
        }

        $response = Http::withHeaders($headers)
            ->acceptJson()
            ->post($baseUrl.'/send/message', [
                'phone' => $this->normalizePhone($notification->recipient),
                'message' => $content->body,
            ]);

        if ($response->failed()) {
            throw new NotificationDeliveryException('GOWA send failed: '.Str::limit($response->body(), 500));
        }

        $id = $response->json('results.message_id')
            ?? $response->json('results.id')
            ?? $response->json('data.message_id')
            ?? $response->json('data.id')
            ?? $response->json('id')
            ?? $response->json('message_id');

        return new ProviderResult(is_string($id) ? $id : null);
    }

    private function normalizePhone(string $phone): string
    {
        $phone = (string) preg_replace('/\D+/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '62'.substr($phone, 1);
        }

        if (! str_starts_with($phone, '62')) {
            return '62'.$phone;
        }

        return $phone;
    }
}
