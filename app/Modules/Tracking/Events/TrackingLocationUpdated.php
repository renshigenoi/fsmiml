<?php

namespace App\Modules\Tracking\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrackingLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $location
     */
    public function __construct(
        public readonly int $workOrderId,
        public readonly int $trackingSessionId,
        public readonly array $location,
        public readonly ?string $realtimeChannel = null,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel("work-order.{$this->workOrderId}")];

        // A2: halaman tracking pelanggan diakses GUEST (tanpa sesi web) sehingga
        // PrivateChannel mustahil di-authorize (/broadcasting/auth butuh login).
        // Audiens channel ini = pemegang token tracking (kredensial = possess),
        // nama channel random 32-char, dan payload hanya posisi teknisi (tanpa PII).
        if (filled($this->realtimeChannel)) {
            $channels[] = new Channel("tracking.{$this->realtimeChannel}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'tracking.location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'tracking_session_id' => $this->trackingSessionId,
            'latitude' => $this->location['latitude'],
            'longitude' => $this->location['longitude'],
            'accuracy_meters' => $this->location['accuracy_meters'] ?? null,
            'speed_mps' => $this->location['speed_mps'] ?? null,
            'recorded_at' => $this->location['recorded_at'],
            'is_mocked' => $this->location['is_mocked'] ?? false,
            'received_at' => $this->location['received_at'] ?? null,
        ];
    }
}
