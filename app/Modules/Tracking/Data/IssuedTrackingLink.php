<?php

namespace App\Modules\Tracking\Data;

use App\Modules\Tracking\Models\TrackingToken;

readonly class IssuedTrackingLink
{
    public function __construct(
        public TrackingToken $trackingToken,
        public string $plainToken,
    ) {}
}
