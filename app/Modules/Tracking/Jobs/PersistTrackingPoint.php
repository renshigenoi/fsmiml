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
        TrackingPoint::query()->create([
            ...$this->location,
            'tracking_session_id' => $this->trackingSessionId,
        ]);
    }
}
