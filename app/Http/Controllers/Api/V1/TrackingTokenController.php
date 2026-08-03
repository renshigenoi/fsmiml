<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Tracking\Enums\TrackingSessionStatus;
use App\Modules\Tracking\Enums\TrackingTokenStatus;
use App\Modules\Tracking\Models\TrackingSession;
use App\Modules\Tracking\Models\TrackingToken;
use App\Modules\Tracking\Services\TrackingTokenService;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;
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

        if ($trackingToken === null
            || $trackingToken->status !== TrackingTokenStatus::Active
            || $trackingToken->expires_at->isPast()
            || $trackingToken->trackingSession->status !== TrackingSessionStatus::Active
            || $trackingToken->trackingSession->workOrder->status !== WorkOrderStatus::OnTheWay) {
            if ($trackingToken?->status === TrackingTokenStatus::Active && $trackingToken->expires_at->isPast()) {
                $trackingToken->update(['status' => TrackingTokenStatus::Expired]);
            }

            throw new NotFoundHttpException;
        }

        $workOrder = $trackingToken->trackingSession->workOrder;
        $location = Cache::get("tracking:session:{$trackingToken->tracking_session_id}:current_location");

        return response()->json([
            'work_order' => [
                'number' => $workOrder->number,
                'status' => $workOrder->status->value,
                'scheduled_start_at' => $workOrder->scheduled_start_at->toISOString(),
            ],
            'destination' => [
                'label' => $workOrder->serviceLocation->label,
                'address' => $workOrder->serviceLocation->address,
                'latitude' => $workOrder->serviceLocation->latitude,
                'longitude' => $workOrder->serviceLocation->longitude,
            ],
            'current_location' => $location,
        ]);
    }
}
