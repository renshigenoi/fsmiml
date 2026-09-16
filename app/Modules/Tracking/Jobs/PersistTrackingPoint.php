<?php

namespace App\Modules\Tracking\Jobs;

use App\Modules\Tracking\Models\TrackingPoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

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

        // B16: pakai waktu SERVER (created_at), bukan recorded_at yang dikontrol
        // perangkat klien — jam klien yang maju bisa memblokir persistensi
        // berjam-jam, jam mundur membuat throttle tidak pernah aktif.
        $recent = TrackingPoint::query()
            ->where('tracking_session_id', $this->trackingSessionId)
            ->where('created_at', '>=', now()->subSeconds($interval))
            ->exists();

        if ($recent) {
            return;
        }

        // B16: gate atomik anti-race dua worker paralel (tabel tidak punya
        // constraint unik untuk ini). Cache::add atomic di Redis; store lain
        // tetap mendapat proteksi best-effort.
        if (! Cache::add("tracking:persist:{$this->trackingSessionId}", true, $interval)) {
            return;
        }

        TrackingPoint::query()->create([
            ...$this->location,
            'tracking_session_id' => $this->trackingSessionId,
        ]);
    }
}
