<?php

namespace Tests\Unit\Notification;

use App\Modules\Notification\Enums\NotificationChannel;
use App\Modules\Notification\Enums\NotificationStatus;
use App\Modules\Notification\Exceptions\NotificationDeliveryException;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Providers\NotificationProviderResolver;
use App\Modules\Notification\Providers\WhatsApp\WhatsAppProviderManager;
use App\Modules\Notification\Services\NotificationContentBuilder;
use App\Modules\Notification\Services\NotificationDeliveryService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationDeliveryServiceTest extends TestCase
{
    #[Test]
    public function it_marks_a_queued_notification_as_sent_after_successful_delivery(): void
    {
        config(['notifications.channels.whatsapp.driver' => 'log']);

        $notification = Mockery::mock(Notification::class)->makePartial();
        $notification->forceFill([
            'channel' => NotificationChannel::WhatsApp,
            'status' => NotificationStatus::Queued,
            'type' => 'assignment_created',
            'recipient' => '08123456789',
        ]);
        $notification->shouldReceive('update')
            ->once()
            ->withArgs(function (array $attributes): bool {
                return ($attributes['status'] ?? null) === NotificationStatus::Sent
                    && ($attributes['sent_at'] ?? null) !== null;
            })
            ->andReturnTrue();

        $this->deliveryService()->deliver($notification);
    }

    #[Test]
    public function it_skips_notifications_that_are_no_longer_queued(): void
    {
        $notification = Mockery::mock(Notification::class)->makePartial();
        $notification->forceFill([
            'channel' => NotificationChannel::WhatsApp,
            'status' => NotificationStatus::Sent,
            'type' => 'assignment_created',
            'recipient' => '08123456789',
        ]);
        $notification->shouldReceive('update')->never();

        $this->deliveryService()->deliver($notification);
    }

    #[Test]
    public function it_reraises_provider_errors_so_the_queue_can_retry(): void
    {
        config([
            'notifications.channels.whatsapp.driver' => 'fonnte',
            'notifications.whatsapp.fonnte.token' => null,
        ]);

        $notification = Mockery::mock(Notification::class)->makePartial();
        $notification->forceFill([
            'channel' => NotificationChannel::WhatsApp,
            'status' => NotificationStatus::Queued,
            'type' => 'assignment_created',
            'recipient' => '08123456789',
        ]);
        $notification->shouldReceive('update')->never();

        $this->expectException(NotificationDeliveryException::class);

        $this->deliveryService()->deliver($notification);
    }

    #[Test]
    public function mark_failed_persists_the_failure_reason(): void
    {
        $notification = Mockery::mock(Notification::class)->makePartial();
        $notification->forceFill([
            'status' => NotificationStatus::Queued,
        ]);
        $notification->shouldReceive('update')
            ->once()
            ->withArgs(function (array $attributes): bool {
                return ($attributes['status'] ?? null) === NotificationStatus::Failed
                    && str_contains((string) ($attributes['failure_reason'] ?? ''), 'provider down');
            })
            ->andReturnTrue();

        $this->deliveryService()->markFailed($notification, 'provider down after 3 attempts');
    }

    private function deliveryService(): NotificationDeliveryService
    {
        return new NotificationDeliveryService(
            new NotificationProviderResolver(new WhatsAppProviderManager),
            new NotificationContentBuilder,
        );
    }
}
