<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'work_order_id' => $this->work_order_id,
            'technician_id' => $this->technician_id,
            'status' => $this->status->value,
            'assigned_at' => $this->assigned_at?->toISOString(),
            'responded_at' => $this->responded_at?->toISOString(),
            'rejected_reason' => $this->when($this->status->value === 'rejected', $this->rejected_reason),
        ];
    }
}
