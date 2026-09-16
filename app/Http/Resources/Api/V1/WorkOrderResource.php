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
            'completion_note' => $this->completion_note,
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
            'car_info' => $this->car_info ?? null,
            'items' => $this->whenLoaded('items'),
            'sales_details' => $this->sales_details ?? [],
            'photos' => $this->whenLoaded('photos', function () {
                $grouped = $this->photos->groupBy('stage');
                return [
                    'before_installation' => $grouped->get('before_installation', collect())->map(fn ($photo) => [
                        'id' => $photo->id,
                        'url' => $photo->url,
                        'original_name' => $photo->original_name,
                        'uploaded_at' => $photo->created_at?->toISOString(),
                    ])->values(),
                    'after_installation' => $grouped->get('after_installation', collect())->map(fn ($photo) => [
                        'id' => $photo->id,
                        'url' => $photo->url,
                        'original_name' => $photo->original_name,
                        'uploaded_at' => $photo->created_at?->toISOString(),
                    ])->values(),
                    // B9: foto penyelesaian (stage 'completion') selama ini tidak
                    // pernah muncul karena grup tidak didefinisikan.
                    'completion' => $grouped->get('completion', collect())->map(fn ($photo) => [
                        'id' => $photo->id,
                        'url' => $photo->url,
                        'original_name' => $photo->original_name,
                        'uploaded_at' => $photo->created_at?->toISOString(),
                    ])->values(),
                ];
            }),
            'assignments' => AssignmentResource::collection($this->whenLoaded('assignments')),
            // A6: whitelist field — JANGAN serialisasi model mentah karena
            // realtime_channel sesi teknisi lain tidak boleh terbaca viewer.
            'tracking_sessions' => $this->whenLoaded('trackingSessions', fn () => $this->trackingSessions->map(fn ($session) => [
                'id' => $session->id,
                'status' => $session->status?->value,
                'started_at' => $session->started_at?->toISOString(),
                'ended_at' => $session->ended_at?->toISOString(),
            ])->values()),
            'status_histories' => $this->whenLoaded('statusHistories'),
        ];
    }
}
