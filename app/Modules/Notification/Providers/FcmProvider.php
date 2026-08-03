<?php

namespace App\Modules\Notification\Providers;

use App\Modules\Notification\Data\NotificationContent;
use App\Modules\Notification\Data\ProviderResult;
use App\Modules\Notification\Exceptions\NotificationDeliveryException;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Providers\Contracts\NotificationProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Firebase Cloud Messaging v1 (HTTP v1 API).
 *
 * Uses the Google service account JSON to mint a short-lived OAuth2 access
 * token (RS256 JWT) and then posts the message to the FCM send endpoint.
 */
final class FcmProvider implements NotificationProvider
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    public function send(Notification $notification, NotificationContent $content): ProviderResult
    {
        $projectId = config('notifications.fcm.project_id');

        if (blank($projectId)) {
            throw new NotificationDeliveryException('FCM is not configured: set FCM_PROJECT_ID.');
        }

        $deviceToken = $this->deviceToken($notification);

        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $content->title,
                        'body' => $content->body,
                    ],
                    'data' => [
                        'type' => $notification->type,
                        'work_order_id' => (string) ($notification->work_order_id ?? ''),
                    ],
                    'android' => [
                        'priority' => 'high',
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new NotificationDeliveryException('FCM send failed: '.Str::limit($response->body(), 500));
        }

        return new ProviderResult($response->json('name'));
    }

    private function deviceToken(Notification $notification): string
    {
        $token = $notification->user?->deviceTokens
            ?->sortByDesc('last_used_at')
            ?->first()
            ?->token;

        if (blank($token)) {
            throw new NotificationDeliveryException('No registered device token found for the recipient user.');
        }

        return $token;
    }

    private function accessToken(): string
    {
        $credentialsPath = config('notifications.fcm.credentials');

        if (blank($credentialsPath) || ! is_file($credentialsPath)) {
            throw new NotificationDeliveryException('FCM is not configured: set FCM_CREDENTIALS to the Google service account JSON path.');
        }

        return Cache::remember('fsm:fcm:access_token', 3300, function () use ($credentialsPath): string {
            try {
                $credentials = json_decode((string) file_get_contents($credentialsPath), true, 512, JSON_THROW_ON_ERROR);
            } catch (Throwable) {
                throw new NotificationDeliveryException('FCM credentials file could not be read as JSON.');
            }

            $now = time();
            $unsigned = $this->base64UrlEncode(json_encode([
                'alg' => 'RS256',
                'typ' => 'JWT',
            ], JSON_THROW_ON_ERROR)).'.'.$this->base64UrlEncode(json_encode([
                'iss' => $credentials['client_email'] ?? null,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => self::TOKEN_URL,
                'iat' => $now,
                'exp' => $now + 3600,
            ], JSON_THROW_ON_ERROR));

            $privateKey = openssl_pkey_get_private($credentials['private_key'] ?? '');

            if ($privateKey === false || ! openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                throw new NotificationDeliveryException('FCM JWT signing failed. Check the service account private key.');
            }

            $response = Http::asForm()->post(self::TOKEN_URL, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $unsigned.'.'.$this->base64UrlEncode($signature),
            ]);

            if ($response->failed() || blank($response->json('access_token'))) {
                throw new NotificationDeliveryException('FCM access token request failed: '.Str::limit($response->body(), 500));
            }

            return (string) $response->json('access_token');
        });
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
