<?php

namespace App\Modules\Assignment\Events;

use App\Modules\Assignment\Enums\AssignmentStatus;
use App\Modules\Assignment\Models\Assignment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssignmentResponded implements ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Assignment $assignment,
        public readonly AssignmentStatus $status,
    ) {}
}
