<?php

namespace App\Modules\Tracking\Jobs;

use App\Modules\Tracking\Models\TrackingPoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PersistTrackingPoint implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $location
     */
    public function __construct(
        public readonly int $trackingSessionId,
        public readonly array $location,
    ) {
        $this->onQueue('tracking');
    }

    public function handle(): void
    {
        // Throttle: hanya persist jika tidak ada titik tersimpan dalam N detik terakhir.
        $interval = (int) config('notifications.tracking.persist_interval_seconds', 30);

        $recent = TrackingPoint::query()
            ->where('tracking_session_id', $this->trackingSessionId)
            ->where('recorded_at', '>=', now()->subSeconds($interval))
            ->exists();

        if ($recent) {
            return;
        }

        TrackingPoint::query()->create([
            ...$this->location,
            'tracking_session_id' => $this->trackingSessionId,
        ]);
    }
}
