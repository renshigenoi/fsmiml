<?php

namespace App\Modules\Tracking\Enums;

enum TrackingTokenStatus: string
{
    case Active = 'active';
    case Revoked = 'revoked';
    case Expired = 'expired';
}
