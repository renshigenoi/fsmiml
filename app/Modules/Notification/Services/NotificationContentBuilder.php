<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Data\NotificationContent;
use App\Modules\Notification\Models\Notification;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;

final class NotificationContentBuilder
{
    public function build(Notification $notification): NotificationContent
    {
        if ($notification->content !== null) {
            return NotificationContent::fromArray($notification->content);
        }

        return match ($notification->type) {
            'assignment_created' => $this->assignmentCreated($notification),
            'assignment_accepted' => $this->assignmentResponded($notification, 'menerima'),
            'assignment_rejected' => $this->assignmentResponded($notification, 'menolak'),
            'assignment_superseded' => $this->assignmentSuperseded($notification),
            'tracking_link_ready' => $this->trackingLinkReady($notification),
            default => $this->workOrderStatus($notification),
        };
    }

    private function assignmentCreated(Notification $notification): NotificationContent
    {
        $workOrder = $notification->workOrder;
        $customer = $workOrder?->customer?->name ?? 'Customer';
        $schedule = $workOrder?->scheduled_start_at?->format('d M Y H:i') ?? 'segera';
        $number = $workOrder?->number ?? '#';

        return new NotificationContent(
            title: 'Tugas Baru Diberikan',
            body: "Anda menerima tugas baru: Work Order {$number} untuk {$customer} pada {$schedule}. Silakan buka aplikasi untuk menerima atau menolak tugas.",
        );
    }

    private function assignmentResponded(Notification $notification, string $action): NotificationContent
    {
        $workOrder = $notification->workOrder;
        $technician = $notification->user?->name ?? 'Teknisi';
        $number = $workOrder?->number ?? '#';

        return new NotificationContent(
            title: $action === 'menerima' ? 'Tugas Diterima' : 'Tugas Ditolak',
            body: "{$technician} {$action} tugas untuk Work Order {$number}. Silakan cek status terkini di dashboard.",
        );
    }

    private function trackingLinkReady(Notification $notification): NotificationContent
    {
        $workOrder = $notification->workOrder;
        $customer = $workOrder?->customer?->name ?? 'Customer';
        $payload = $workOrder?->salesOrder?->source_payload ?? [];
        $car = trim(
            trim((string) ($payload['car_brand'] ?? '')).' '.trim((string) ($payload['car_model'] ?? ''))
        );
        $chassis = trim((string) ($payload['chassis_no'] ?? ''));
        $police = trim((string) ($payload['police_no'] ?? ''));
        $film = trim((string) ($payload['window_film_desc'] ?? ''));
        $number = $workOrder?->number ?? '-';

        $lines = [
            "Halo Bapak / Ibu {$customer} 👋",
            '',
            'Teknisi kami sedang menuju lokasi pemasangan Anda 🚗💨',
            "📋 No. Order: {$number}",
        ];

        if ($car !== '') {
            $lines[] = "🚘 Kendaraan: {$car}";
        }

        if ($chassis !== '' || $police !== '') {
            $lines[] = '🔢 Chassis # / Police #: '
                .($chassis !== '' ? $chassis : '-')
                .' / '
                .($police !== '' ? $police : '-');
        }

        if ($film !== '') {
            $lines[] = "🪟 Window Film: {$film}";
        }

        $lines[] = '';
        $lines[] = 'Link tracking pemasangan akan menyusul di pesan ini.';

        return new NotificationContent(
            title: 'Link Tracking Pemasangan',
            body: implode("\n", $lines),
            trackingUrl: null,
        );
    }

    private function assignmentSuperseded(Notification $notification): NotificationContent
    {
        $workOrder = $notification->workOrder;
        $number = $workOrder?->number ?? '#';

        return new NotificationContent(
            title: 'Tugas Sudah Diambil Rekan',
            body: "Tugas untuk Work Order {$number} sudah diambil oleh rekan teknisi lain, sehingga penugasan Anda tidak diperlukan lagi.",
        );
    }

    private function workOrderStatus(Notification $notification): NotificationContent
    {
        $status = $notification->type === 'work_order_waiting_acceptance'
            ? WorkOrderStatus::WaitingAcceptance
            : WorkOrderStatus::tryFrom(str_replace('work_order_', '', (string) $notification->type));

        $labels = [
            'draft' => 'Draft',
            'waiting_acceptance' => 'Menunggu Konfirmasi Teknisi',
            'accepted' => 'Tugas Diterima',
            'on_the_way' => 'Perjalanan Dimulai',
            'arrived' => 'Teknisi Tiba di Lokasi',
            'installation' => 'Pemasangan Dimulai',
            'finished' => 'Pekerjaan Selesai',
            'rejected' => 'Tugas Ditolak',
            'cancelled' => 'Pekerjaan Dibatalkan',
            'failed' => 'Pekerjaan Gagal',
        ];

        $label = $status !== null ? ($labels[$status->value] ?? ucfirst($status->value)) : ucfirst((string) $notification->type);
        $workOrder = $notification->workOrder;
        $number = $workOrder?->number ?? '#';
        $customer = $workOrder?->customer?->name ?? 'Customer';

        return new NotificationContent(
            title: $label,
            body: "Work Order {$number} untuk {$customer} sekarang berstatus: {$label}.",
        );
    }
}
