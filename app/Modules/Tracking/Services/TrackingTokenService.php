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
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class TrackingTokenService
{
    /**
     * Ambil link tracking yang bisa ditampilkan di dashboard.
     *
     * Memakai token aktif yang sudah punya plaintext terenkripsi. Kalau token
     * aktif dibuat oleh versi kode lama (tanpa token_plain_encrypted), buat
     * token baru TANPA merevoke token lama — link WhatsApp customer tetap valid.
     */
    public function ensurePlaintextLink(TrackingSession $trackingSession): ?IssuedTrackingLink
    {
        $session = TrackingSession::query()
            ->with('workOrder')
            ->find($trackingSession->getKey());

        if (
            $session === null
            || $session->status !== TrackingSessionStatus::Active
            || $session->workOrder->status !== WorkOrderStatus::OnTheWay
        ) {
            return null;
        }

        $existing = TrackingToken::query()
            ->where('tracking_session_id', $session->getKey())
            ->where('status', TrackingTokenStatus::Active->value)
            ->whereNotNull('token_plain_encrypted')
            ->orderByDesc('id')
            ->first();

        if ($existing !== null) {
            return new IssuedTrackingLink($existing, Crypt::decryptString($existing->token_plain_encrypted));
        }

        $plainToken = Str::random(64);

        $token = TrackingToken::query()->create([
            'tracking_session_id' => $session->getKey(),
            'token_hash' => hash('sha256', $plainToken),
            'token_plain_encrypted' => Crypt::encryptString($plainToken),
            'status' => TrackingTokenStatus::Active,
            'expires_at' => now()->addHours((float) config('notifications.tracking.token_ttl_hours')),
        ]);

        return new IssuedTrackingLink($token, $plainToken);
    }

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
                'token_plain_encrypted' => Crypt::encryptString($plainToken),
                'status' => TrackingTokenStatus::Active,
                'expires_at' => now()->addHours((float) config('notifications.tracking.token_ttl_hours')),
            ]);

            return new IssuedTrackingLink($token, $plainToken);
        });
    }
}
