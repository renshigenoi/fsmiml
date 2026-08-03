<?php

namespace App\Modules\WorkOrder\Enums;

enum WorkOrderStatus: string
{
    case Draft = 'draft';
    case WaitingAcceptance = 'waiting_acceptance';
    case Accepted = 'accepted';
    case OnTheWay = 'on_the_way';
    case Arrived = 'arrived';
    case Installation = 'installation';
    case Finished = 'finished';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Failed = 'failed';
}
