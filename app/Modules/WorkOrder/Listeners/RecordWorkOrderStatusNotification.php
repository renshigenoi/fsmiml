<?php

namespace App\Modules\WorkOrder\Listeners;

use App\Modules\Assignment\Enums\AssignmentStatus;
use App\Modules\Customer\Models\Customer;
use App\Modules\Notification\Enums\NotificationChannel;
use App\Modules\Notification\Services\NotificationAuditService;
use App\Modules\Tracking\Enums\TrackingSessionStatus;
use App\Modules\Tracking\Models\TrackingSession;
use App\Modules\Tracking\Services\TrackingTokenService;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;
use App\Modules\WorkOrder\Events\WorkOrderStatusChanged;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecordWorkOrderStatusNotification implements ShouldQueue
{
    public function __construct(private readonly NotificationAuditService $notifications) {}

    public function handle(WorkOrderStatusChanged $event): void
    {
        $workOrder = $event->workOrder->loadMissing(['assignments.assignedBy', 'customer']);
        $assignment = $workOrder->assignments->sortByDesc('assigned_at')->first();

        if ($assignment?->assignedBy !== null) {
            $this->notifications->queue(
                $assignment->assignedBy,
                $workOrder,
                NotificationChannel::Push,
                "work_order_{$event->toStatus->value}",
                $assignment->assignedBy->email,
            );
        }

        if ($event->toStatus === WorkOrderStatus::OnTheWay) {
            $this->queueCustomerTrackingNotifications($workOrder);
        }
    }

    private function queueCustomerTrackingNotifications(
        WorkOrder $workOrder,
    ): void {
        $workOrder->loadMissing(['trackingSessions']);

        $session = $workOrder->trackingSessions
            ->first(fn (TrackingSession $trackingSession): bool => $trackingSession->status === TrackingSessionStatus::Active);
        $customer = $workOrder->customer;

        if ($session === null || $customer === null) {
            return;
        }

        $content = $this->customerTrackingContent($workOrder, $customer, $session);

        if (filled($customer->phone)) {
            $this->notifications->queue(
                null,
                $workOrder,
                NotificationChannel::WhatsApp,
                'tracking_link_ready',
                $customer->phone,
                $content,
            );
        }

        if (filled($customer->email)) {
            $this->notifications->queue(
                null,
                $workOrder,
                NotificationChannel::Email,
                'tracking_link_ready',
                $customer->email,
                $content,
            );
        }
    }

    /**
     * @return array{title: string, body: string, tracking_url: string|null}
     */
    private function customerTrackingContent(
        WorkOrder $workOrder,
        Customer $customer,
        TrackingSession $session,
    ): array {
        $trackingUrl = null;

        try {
            $issued = app(TrackingTokenService::class)->issue($session);
            $publicBase = rtrim((string) config('notifications.tracking.public_url'), '/');
            $trackingUrl = ($publicBase !== '' ? $publicBase : rtrim((string) url('/tracking'), '/'))
                .'/'.$issued->plainToken;
        } catch (Throwable $exception) {
            Log::warning('Failed to issue tracking token for customer notification.', [
                'work_order_id' => $workOrder->getKey(),
                'error' => $exception->getMessage(),
            ]);
        }

        $payload = $workOrder->salesOrder?->source_payload ?? [];
        $car = trim(
            trim((string) ($payload['car_brand'] ?? '')).' '.trim((string) ($payload['car_model'] ?? ''))
        );
        $technician = $workOrder->assignments
            ->firstWhere('status', AssignmentStatus::Accepted)
            ?->technician?->user?->name;

        $lines = [
            "Halo {$customer->name} 👋",
            '',
            'Teknisi kami sedang menuju lokasi pemasangan Anda 🚗💨',
            "📋 No. Order: {$workOrder->number}",
        ];

        if ($car !== '') {
            $lines[] = "🚘 Kendaraan: {$car}";
        }

        if (filled($technician)) {
            $lines[] = "🧑‍🔧 Teknisi: {$technician}";
        }

        if ($trackingUrl !== null) {
            $lines[] = '';
            $lines[] = '👉 Pantau posisi teknisi secara langsung lewat link berikut:';
            $lines[] = $trackingUrl;
            $lines[] = '';
            $lines[] = '_Link berlaku selama perjalanan berlangsung._';
        }

        $body = implode("\n", $lines);

        return [
            'title' => 'Link Tracking Pemasangan',
            'body' => $body,
            'tracking_url' => $trackingUrl,
        ];
    }
}
