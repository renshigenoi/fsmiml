<?php

namespace App\Modules\Tracking\Services;

use App\Modules\Tracking\Data\IssuedTrackingLink;
use App\Modules\Tracking\Enums\TrackingSessionStatus;
use App\Modules\Tracking\Enums\TrackingTokenStatus;
use App\Modules\Tracking\Exceptions\InvalidTrackingToken;
use App\Modules\Tracking\Models\TrackingSession;
use App\Modules\Tracking\Models\TrackingToken;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrackingTokenService
{
    public function issue(TrackingSession $trackingSession): IssuedTrackingLink
    {
        return DB::transaction(function () use ($trackingSession): IssuedTrackingLink {
            $session = TrackingSession::query()
                ->with('workOrder')
                ->lockForUpdate()
                ->findOrFail($trackingSession->getKey());

            if ($session->status !== TrackingSessionStatus::Active || $session->workOrder->status !== WorkOrderStatus::OnTheWay) {
                throw new InvalidTrackingToken('A tracking link can only be issued for an active trip.');
            }

            $plainToken = Str::random(64);

            TrackingToken::query()
                ->where('tracking_session_id', $session->getKey())
                ->where('status', TrackingTokenStatus::Active->value)
                ->update([
                    'status' => TrackingTokenStatus::Revoked,
                    'revoked_at' => now(),
                ]);

            $token = TrackingToken::query()->create([
                'tracking_session_id' => $session->getKey(),
                'token_hash' => hash('sha256', $plainToken),
                'status' => TrackingTokenStatus::Active,
                'expires_at' => now()->addHours(8),
            ]);

            return new IssuedTrackingLink($token, $plainToken);
        });
    }
}
