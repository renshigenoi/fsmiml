<?php

namespace App\Modules\Attendance\Jobs;

use App\Modules\Attendance\Models\AttendanceRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ResolveAttendanceAddress implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 15;
    public array $backoff = [30, 120];

    public function __construct(
        private readonly int $attendanceRecordId,
        private readonly string $prefix,
    ) {}

    public function handle(): void
    {
        if (! in_array($this->prefix, ['check_in', 'check_out'], true)) {
            return;
        }

        $record = AttendanceRecord::query()->find($this->attendanceRecordId);
        if (! $record || $record->{$this->prefix.'_address'}) {
            return;
        }

        $latitude = $record->{$this->prefix.'_latitude'};
        $longitude = $record->{$this->prefix.'_longitude'};
        if ($latitude === null || $longitude === null) {
            return;
        }

        // Pembulatan 4 digit (~11 m) membuat absen di lokasi sama memakai hasil cache.
        $cacheKey = 'attendance:address:'.number_format((float) $latitude, 4, '.', '')
            .','.number_format((float) $longitude, 4, '.', '');
        $address = Cache::remember($cacheKey, now()->addDays(30), function () use ($latitude, $longitude): string {
            $response = Http::connectTimeout(3)->timeout(6)->retry(2, 500, throw: false)
                ->withHeaders(['User-Agent' => 'IML-FSM-App/1.0 (admin@indomotorlestari.co.id)'])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'jsonv2',
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'zoom' => 18,
                ]);

            $address = $response->successful() ? $response->json('display_name') : null;
            if (! is_string($address) || $address === '') {
                throw new RuntimeException('Reverse geocoding belum tersedia.');
            }

            return $address;
        });

        $record->update([$this->prefix.'_address' => $address]);
    }
}
