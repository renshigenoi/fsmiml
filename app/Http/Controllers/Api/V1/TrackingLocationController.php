<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTrackingLocationRequest;
use App\Modules\Tracking\Enums\TrackingSessionStatus;
use App\Modules\Tracking\Events\TrackingLocationUpdated;
use App\Modules\Tracking\Jobs\PersistTrackingPoint;
use App\Modules\Tracking\Models\TrackingSession;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class TrackingLocationController extends Controller
{
    public function store(StoreTrackingLocationRequest $request, TrackingSession $trackingSession): JsonResponse
    {
        $this->authorize('submitLocation', $trackingSession);

        if ($trackingSession->status !== TrackingSessionStatus::Active
            || $trackingSession->workOrder->status !== WorkOrderStatus::OnTheWay) {
            throw ValidationException::withMessages([
                'tracking_session_id' => 'Location updates are only accepted for an active on-the-way tracking session.',
            ]);
        }

        $location = [
            ...$request->validated(),
            'received_at' => now()->toISOString(),
        ];

        Cache::put("tracking:session:{$trackingSession->getKey()}:current_location", $location, now()->addMinutes(2));
        PersistTrackingPoint::dispatch($trackingSession->getKey(), $location);
        TrackingLocationUpdated::dispatch($trackingSession->work_order_id, $trackingSession->getKey(), $location);

        return response()->json(status: 202);
    }
}
