<?php

namespace Tests\Unit\Notification;

use App\Models\User;
use App\Modules\Identity\Models\UserDeviceToken;
use App\Modules\Notification\Data\NotificationContent;
use App\Modules\Notification\Enums\NotificationChannel;
use App\Modules\Notification\Exceptions\NotificationDeliveryException;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Providers\FcmProvider;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FcmProviderTest extends TestCase
{
    private string $credentialsPath;

    protected function setUp(): void
    {
        parent::setUp();

        $opensslConfig = dirname(PHP_BINARY).DIRECTORY_SEPARATOR.'extras'.DIRECTORY_SEPARATOR.'ssl'.DIRECTORY_SEPARATOR.'openssl.cnf';
        $key = openssl_pkey_new([
            'config' => is_file($opensslConfig) ? $opensslConfig : null,
            'private_key_bits' => 1024,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $this->assertNotFalse($key);

        $this->assertTrue(openssl_pkey_export($key, $privateKey, null, ['config' => $opensslConfig]));
        $this->credentialsPath = tempnam(sys_get_temp_dir(), 'fcm-').'.json';
        file_put_contents($this->credentialsPath, json_encode([
            'client_email' => 'fsm@project.iam.gserviceaccount.com',
            'private_key' => $privateKey,
        ], JSON_THROW_ON_ERROR));

        config([
            'notifications.fcm.project_id' => 'fsm-project',
            'notifications.fcm.credentials' => $this->credentialsPath,
            'notifications.fcm.dry_run' => false,
        ]);
    }

    protected function tearDown(): void
    {
        @unlink($this->credentialsPath);
        parent::tearDown();
    }

    #[Test]
    public function it_mints_an_access_token_and_sends_through_the_fcm_v1_api(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-access-token'], 200),
            'fcm.googleapis.com/*' => Http::response(['name' => 'projects/fsm-project/messages/msg-123'], 200),
        ]);

        $result = (new FcmProvider)->send($this->notification(), new NotificationContent('Tugas Baru', 'WO-1 untuk Budi'));

        $this->assertSame('projects/fsm-project/messages/msg-123', $result->messageId);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://oauth2.googleapis.com/token'
                && str_contains($request->body(), 'grant_type=');
        });

        Http::assertSent(function ($request): bool {
            return str_contains($request->url(), '/messages:send')
                && $request['message']['token'] === 'device-token-123'
                && $request['message']['notification']['title'] === 'Tugas Baru';
        });
    }

    #[Test]
    public function it_throws_when_fcm_credentials_are_not_configured(): void
    {
        config(['notifications.fcm.credentials' => null]);

        $this->expectException(NotificationDeliveryException::class);
        $this->expectExceptionMessage('FCM is not configured');

        (new FcmProvider)->send($this->notification(), new NotificationContent('T', 'B'));
    }

    #[Test]
    public function it_throws_when_the_recipient_has_no_device_token(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'token'], 200),
        ]);

        $notification = $this->notification();
        $notification->setRelation('user', new User(['name' => 'Tanpa Token']));

        $this->expectException(NotificationDeliveryException::class);
        $this->expectExceptionMessage('No registered device token');

        (new FcmProvider)->send($notification, new NotificationContent('T', 'B'));
    }

    private function notification(): Notification
    {
        $deviceToken = new UserDeviceToken(['token' => 'device-token-123', 'last_used_at' => now()]);
        $user = new User(['name' => 'Teknisi A']);
        $user->setRelation('deviceTokens', collect([$deviceToken]));

        $notification = new Notification([
            'channel' => NotificationChannel::Push,
            'type' => 'assignment_created',
            'recipient' => 'teknisi@example.com',
            'work_order_id' => 1,
        ]);
        $notification->setRelation('user', $user);

        return $notification;
    }
}
