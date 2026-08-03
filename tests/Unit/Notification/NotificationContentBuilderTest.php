<?php

namespace Tests\Unit\Notification;

use App\Modules\Customer\Models\Customer;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Services\NotificationContentBuilder;
use App\Modules\WorkOrder\Models\WorkOrder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationContentBuilderTest extends TestCase
{
    private NotificationContentBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new NotificationContentBuilder;
    }

    #[Test]
    public function it_builds_an_assignment_created_message_from_loaded_relations(): void
    {
        $notification = $this->notification(['type' => 'assignment_created']);

        $content = $this->builder->build($notification);

        $this->assertSame('Tugas Baru Diberikan', $content->title);
        $this->assertStringContainsString('WO-2026-0001', $content->body);
        $this->assertStringContainsString('Budi Santoso', $content->body);
    }

    #[Test]
    public function it_builds_work_order_status_messages(): void
    {
        $content = $this->builder->build($this->notification(['type' => 'work_order_on_the_way']));

        $this->assertSame('Perjalanan Dimulai', $content->title);
        $this->assertStringContainsString('WO-2026-0001', $content->body);
    }

    #[Test]
    public function it_builds_a_superseded_assignment_message(): void
    {
        $content = $this->builder->build($this->notification(['type' => 'assignment_superseded']));

        $this->assertSame('Tugas Sudah Diambil Rekan', $content->title);
        $this->assertStringContainsString('WO-2026-0001', $content->body);
    }

    #[Test]
    public function it_uses_a_stored_payload_when_available(): void
    {
        $notification = $this->notification(['type' => 'tracking_link_ready']);
        $notification->forceFill(['content' => [
            'title' => 'Link Tracking Pemasangan',
            'body' => 'Pantau di: https://tracking.fsm.com/abc',
            'tracking_url' => 'https://tracking.fsm.com/abc',
        ]]);

        $content = $this->builder->build($notification);

        $this->assertSame('https://tracking.fsm.com/abc', $content->trackingUrl);
        $this->assertStringContainsString('tracking.fsm.com', $content->body);
    }

    #[Test]
    public function it_falls_back_to_a_generic_tracking_message_without_a_payload(): void
    {
        $content = $this->builder->build($this->notification(['type' => 'tracking_link_ready']));

        $this->assertSame('Link Tracking Pemasangan', $content->title);
        $this->assertNull($content->trackingUrl);
    }

    private function notification(array $attributes): Notification
    {
        $customer = new Customer(['name' => 'Budi Santoso']);
        $workOrder = new WorkOrder([
            'number' => 'WO-2026-0001',
            'scheduled_start_at' => '2026-08-04 09:00:00',
        ]);
        $workOrder->setRelation('customer', $customer);

        $notification = new Notification(array_merge([
            'channel' => 'push',
            'status' => 'queued',
            'recipient' => 'recipient@example.com',
        ], $attributes));
        $notification->setRelation('workOrder', $workOrder);

        return $notification;
    }
}
