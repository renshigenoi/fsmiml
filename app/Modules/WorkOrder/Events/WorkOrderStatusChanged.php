<?php

namespace App\Modules\WorkOrder\Events;

use App\Models\User;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkOrderStatusChanged implements ShouldDispatchAfterCommit, ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly WorkOrder $workOrder,
        public readonly WorkOrderStatus $fromStatus,
        public readonly WorkOrderStatus $toStatus,
        public readonly ?User $actor,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('dashboard')];
    }

    public function broadcastAs(): string
    {
        return 'work-order.status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'work_order_id' => $this->workOrder->getKey(),
            'number' => $this->workOrder->number,
            'from_status' => $this->fromStatus->value,
            'to_status' => $this->toStatus->value,
            'changed_at' => now()->toIso8601String(),
        ];
    }
}
