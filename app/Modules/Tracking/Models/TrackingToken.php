<?php

namespace App\Modules\Tracking\Models;

use App\Modules\Tracking\Enums\TrackingTokenStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingToken extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'tracking_session_id',
        'token_hash',
        'status',
        'expires_at',
        'revoked_at',
    ];

    protected $hidden = [
        'token_hash',
    ];

    protected function casts(): array
    {
        return [
            'status' => TrackingTokenStatus::class,
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function trackingSession(): BelongsTo
    {
        return $this->belongsTo(TrackingSession::class);
    }
}
