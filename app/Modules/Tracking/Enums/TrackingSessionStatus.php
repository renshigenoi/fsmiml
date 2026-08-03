<?php

namespace App\Modules\Tracking\Enums;

enum TrackingSessionStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
}
