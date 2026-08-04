<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Tracking\Enums\TrackingTokenStatus;
use App\Modules\Tracking\Models\TrackingSession;
use App\Modules\Tracking\Models\TrackingPoint;
use App\Modules\Tracking\Models\TrackingToken;
use App\Modules\Tracking\Services\TrackingTokenService;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;
use App\Modules\WorkOrder\Models\WorkOrderStatusHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TrackingTokenController extends Controller
{
    public function store(TrackingSession $trackingSession, TrackingTokenService $tokens): JsonResponse
    {
        $this->authorize('issueToken', $trackingSession);

        $issued = $tokens->issue($trackingSession);

        return response()->json([
            'tracking_url' => url("/api/v1/public/tracking/{$issued->plainToken}"),
            'expires_at' => $issued->trackingToken->expires_at->toISOString(),
        ], 201);
    }

    public function show(string $token): JsonResponse
    {
        $trackingToken = TrackingToken::query()
            ->with(['trackingSession.workOrder.serviceLocation'])
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if ($trackingToken === null) {
            throw new NotFoundHttpException;
        }

        if ($trackingToken->expires_at->isPast()) {
            if ($trackingToken->status === TrackingTokenStatus::Active) {
                $trackingToken->update(['status' => TrackingTokenStatus::Expired]);
            }

            throw new NotFoundHttpException;
        }

        $workOrder = $trackingToken->trackingSession->workOrder;

        $terminal = match (true) {
            $workOrder->status === WorkOrderStatus::Finished => 'finished',
            in_array($workOrder->status, [WorkOrderStatus::Cancelled, WorkOrderStatus::Failed], true) => $workOrder->status->value,
            default => null,
        };

        if ($terminal !== null) {
            return $this->payload($trackingToken, $terminal, null);
        }

        if ($trackingToken->status !== TrackingTokenStatus::Active
            || ! in_array($workOrder->status, [
                WorkOrderStatus::OnTheWay,
                WorkOrderStatus::Arrived,
                WorkOrderStatus::Installation,
            ], true)) {
            throw new NotFoundHttpException;
        }

        $location = Cache::get("tracking:session:{$trackingToken->tracking_session_id}:current_location");

        return $this->payload($trackingToken, 'on_the_way', $location);
    }

    private function payload(TrackingToken $trackingToken, string $status, ?array $location): JsonResponse
    {
        $workOrder = $trackingToken->trackingSession->workOrder;

        $tripPoints = [];
        $tripSummary = null;
        if ($status !== 'on_the_way') {
            $points = TrackingPoint::query()
                ->where('tracking_session_id', $trackingToken->tracking_session_id)
                ->orderBy('recorded_at')
                ->get(['latitude', 'longitude', 'recorded_at']);

            $total = $points->count();
            $step = $total > 300 ? (int) ceil($total / 300) : 1;

            $tripPoints = $points
                ->filter(fn ($point, $index): bool => $index % $step === 0 || $index === $total - 1)
                ->values()
                ->map(fn ($point): array => [
                    'latitude' => (float) $point->latitude,
                    'longitude' => (float) $point->longitude,
                    'recorded_at' => $point->recorded_at->toIso8601String(),
                ])
                ->all();

            if ($total > 0) {
                $first = $points->first();
                $last = $points->last();
                $distance = 0;
                $previous = null;

                foreach ($points as $point) {
                    if ($previous !== null) {
                        $distance += $this->haversine(
                            (float) $previous->latitude,
                            (float) $previous->longitude,
                            (float) $point->latitude,
                            (float) $point->longitude,
                        );
                    }
                    $previous = $point;
                }

                $finishedAt = WorkOrderStatusHistory::query()
                    ->where('work_order_id', $workOrder->getKey())
                    ->where('to_status', WorkOrderStatus::Finished->value)
                    ->latest('occurred_at')
                    ->value('occurred_at');

                $tripSummary = [
                    'started_at' => $first->recorded_at->toIso8601String(),
                    'arrived_at' => $last->recorded_at->toIso8601String(),
                    'finished_at' => $finishedAt?->toIso8601String(),
                    'distance_m' => (int) round($distance),
                    'duration_s' => (int) max(0, $last->recorded_at->diffInSeconds($first->recorded_at)),
                ];
            }
        }

        return response()->json([
            'status' => $status,
            'work_order' => [
                'number' => $workOrder->number,
                'status' => $workOrder->status->value,
                'scheduled_start_at' => $workOrder->scheduled_start_at?->toISOString(),
            ],
            'destination' => [
                'label' => $workOrder->serviceLocation?->label,
                'address' => $workOrder->serviceLocation?->address,
                'latitude' => $workOrder->serviceLocation?->latitude,
                'longitude' => $workOrder->serviceLocation?->longitude,
            ],
            'realtime_channel' => $trackingToken->trackingSession->realtime_channel,
            'current_location' => $location,
            'trip_points' => $tripPoints,
            'trip_summary' => $tripSummary,
        ]);
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $radius = 6371000;
        $toRad = fn (float $degrees): float => $degrees * M_PI / 180;
        $dLat = $toRad($lat2 - $lat1);
        $dLng = $toRad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos($toRad($lat1)) * cos($toRad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $radius * asin(sqrt($a));
    }
}
