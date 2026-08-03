<?php

namespace Tests\Unit\Notification;

use App\Modules\Notification\Data\NotificationContent;
use App\Modules\Notification\Enums\NotificationChannel;
use App\Modules\Notification\Exceptions\NotificationDeliveryException;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Providers\LogNotificationProvider;
use App\Modules\Notification\Providers\WhatsApp\FonnteProvider;
use App\Modules\Notification\Providers\WhatsApp\GowaProvider;
use App\Modules\Notification\Providers\WhatsApp\MetaProvider;
use App\Modules\Notification\Providers\WhatsApp\WablasProvider;
use App\Modules\Notification\Providers\WhatsApp\WhatsAppProviderManager;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WhatsAppProvidersTest extends TestCase
{
    #[Test]
    public function fonnte_sends_to_the_fonnte_api_with_an_auth_header(): void
    {
        config([
            'notifications.whatsapp.fonnte.token' => 'fonnte-token',
            'notifications.whatsapp.default_country_code' => '62',
        ]);
        Http::fake(['api.fonnte.com/*' => Http::response(['id' => 'wa-1', 'status' => true], 200)]);

        $result = (new FonnteProvider)->send($this->notification(), new NotificationContent('T', 'Halo pelanggan'));

        $this->assertSame('wa-1', $result->messageId);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://api.fonnte.com/send'
                && $request->hasHeader('Authorization', 'fonnte-token')
                && str_contains($request->body(), 'target=08123456789')
                && str_contains($request->body(), 'message=Halo+pelanggan');
        });
    }

    #[Test]
    public function meta_sends_through_the_whatsapp_business_cloud_api(): void
    {
        config([
            'notifications.whatsapp.meta.token' => 'meta-token',
            'notifications.whatsapp.meta.phone_number_id' => '123456789',
        ]);
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.ABC']]], 200)]);

        $result = (new MetaProvider)->send($this->notification(), new NotificationContent('T', 'Halo pelanggan'));

        $this->assertSame('wamid.ABC', $result->messageId);

        Http::assertSent(function ($request): bool {
            return str_contains($request->url(), '/123456789/messages')
                && $request->hasHeader('Authorization', 'Bearer meta-token')
                && $request['messaging_product'] === 'whatsapp'
                && $request['to'] === '08123456789'
                && $request['text']['body'] === 'Halo pelanggan';
        });
    }

    #[Test]
    public function wablas_sends_to_the_configured_domain(): void
    {
        config([
            'notifications.whatsapp.wablas.token' => 'wablas-token',
            'notifications.whatsapp.wablas.domain' => 'https://wa.example.com',
        ]);
        Http::fake(['wa.example.com/*' => Http::response(['data' => ['id' => 'wablas-1']], 200)]);

        $result = (new WablasProvider)->send($this->notification(), new NotificationContent('T', 'Halo pelanggan'));

        $this->assertSame('wablas-1', $result->messageId);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://wa.example.com/api/send-message'
                && $request['phone'] === '08123456789'
                && $request['message'] === 'Halo pelanggan'
                && $request['token'] === 'wablas-token';
        });
    }

    #[Test]
    public function gowa_sends_to_the_configured_gateway_with_basic_auth(): void
    {
        config([
            'notifications.whatsapp.gowa.base_url' => 'https://wa.indomotorlestari.co.id',
            'notifications.whatsapp.gowa.api_key' => 'gowa-secret',
            'notifications.whatsapp.gowa.basic_user' => 'admin',
            'notifications.whatsapp.gowa.basic_pass' => 'PasswordK0s0ng',
            'notifications.whatsapp.gowa.device_id' => 'device-notifwa-tracking',
        ]);
        Http::fake(['wa.indomotorlestari.co.id/*' => Http::response(['id' => 'gowa-1'], 200)]);

        $result = (new GowaProvider)->send($this->notification(), new NotificationContent('T', 'Halo pelanggan'));

        $this->assertSame('gowa-1', $result->messageId);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://wa.indomotorlestari.co.id/send/message'
                && $request->hasHeader('Authorization', 'Basic '.base64_encode('admin:PasswordK0s0ng'))
                && $request->hasHeader('X-Device-Id', 'device-notifwa-tracking')
                && $request['phone'] === '628123456789'
                && $request['message'] === 'Halo pelanggan';
        });
    }

    #[Test]
    public function gowa_falls_back_to_a_bearer_token_when_basic_auth_is_not_configured(): void
    {
        config([
            'notifications.whatsapp.gowa.base_url' => 'https://wa.indomotorlestari.co.id',
            'notifications.whatsapp.gowa.api_key' => 'gowa-secret',
            'notifications.whatsapp.gowa.basic_user' => null,
            'notifications.whatsapp.gowa.basic_pass' => null,
        ]);
        Http::fake(['wa.indomotorlestari.co.id/*' => Http::response(['id' => 'gowa-2'], 200)]);

        (new GowaProvider)->send($this->notification(), new NotificationContent('T', 'Halo pelanggan'));

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer gowa-secret'));
    }

    #[Test]
    public function gowa_omits_the_device_header_when_no_device_is_configured(): void
    {
        config([
            'notifications.whatsapp.gowa.base_url' => 'https://wa.indomotorlestari.co.id',
            'notifications.whatsapp.gowa.api_key' => 'gowa-secret',
            'notifications.whatsapp.gowa.basic_user' => null,
            'notifications.whatsapp.gowa.basic_pass' => null,
            'notifications.whatsapp.gowa.device_id' => null,
        ]);
        Http::fake(['wa.indomotorlestari.co.id/*' => Http::response(['id' => 'gowa-3'], 200)]);

        (new GowaProvider)->send($this->notification(), new NotificationContent('T', 'Halo pelanggan'));

        Http::assertSent(fn ($request) => ! $request->hasHeader('X-Device-Id'));
    }

    #[Test]
    public function gowa_normalizes_local_phone_numbers_to_international_format(): void
    {
        config([
            'notifications.whatsapp.gowa.base_url' => 'https://wa.indomotorlestari.co.id',
            'notifications.whatsapp.gowa.api_key' => 'gowa-secret',
        ]);
        Http::fake(['wa.indomotorlestari.co.id/*' => Http::response(['id' => 'gowa-2'], 200)]);

        $notification = new Notification([
            'channel' => NotificationChannel::WhatsApp,
            'type' => 'tracking_link_ready',
            'recipient' => '+62 812-3456-789',
        ]);

        (new GowaProvider)->send($notification, new NotificationContent('T', 'Halo pelanggan'));

        Http::assertSent(fn ($request) => $request['phone'] === '628123456789');
    }

    #[Test]
    public function providers_throw_a_clear_error_when_credentials_are_missing(): void
    {
        config(['notifications.whatsapp.fonnte.token' => null]);

        $this->expectException(NotificationDeliveryException::class);
        $this->expectExceptionMessage('Fonnte is not configured');

        (new FonnteProvider)->send($this->notification(), new NotificationContent('T', 'B'));
    }

    #[Test]
    public function the_manager_resolves_the_expected_driver(): void
    {
        $manager = new WhatsAppProviderManager;

        $this->assertInstanceOf(FonnteProvider::class, $manager->driver('fonnte'));
        $this->assertInstanceOf(WablasProvider::class, $manager->driver('wablas'));
        $this->assertInstanceOf(MetaProvider::class, $manager->driver('meta'));
        $this->assertInstanceOf(GowaProvider::class, $manager->driver('gowa'));
        $this->assertInstanceOf(LogNotificationProvider::class, $manager->driver('log'));
    }

    private function notification(): Notification
    {
        return new Notification([
            'channel' => NotificationChannel::WhatsApp,
            'type' => 'tracking_link_ready',
            'recipient' => '08123456789',
        ]);
    }
}
