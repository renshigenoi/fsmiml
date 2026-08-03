<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'sales_order_id' => $this->sales_order_id,
            'customer_id' => $this->customer_id,
            'service_location_id' => $this->service_location_id,
            'work_type' => $this->work_type,
            'status' => $this->status->value,
            'scheduled_start_at' => $this->scheduled_start_at->toISOString(),
            'scheduled_end_at' => $this->scheduled_end_at?->toISOString(),
            'notes' => $this->notes,
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
                'email' => $this->customer->email,
            ]),
            'service_location' => $this->whenLoaded('serviceLocation', fn () => [
                'id' => $this->serviceLocation->id,
                'label' => $this->serviceLocation->label,
                'address' => $this->serviceLocation->address,
                'city' => $this->serviceLocation->city,
                'province' => $this->serviceLocation->province,
                'postal_code' => $this->serviceLocation->postal_code,
                'latitude' => $this->serviceLocation->latitude,
                'longitude' => $this->serviceLocation->longitude,
            ]),
            'items' => $this->whenLoaded('items'),
            'assignments' => AssignmentResource::collection($this->whenLoaded('assignments')),
            'tracking_sessions' => $this->whenLoaded('trackingSessions'),
            'status_histories' => $this->whenLoaded('statusHistories'),
        ];
    }
}
