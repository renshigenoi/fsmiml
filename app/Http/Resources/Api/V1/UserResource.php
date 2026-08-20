<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role?->value,
            'technician_id' => $this->technician?->getKey(),
            'has_pin' => ! is_null($this->pin_hash),
            'allow_fake_gps' => (bool) $this->allow_fake_gps,
        ];
    }
}
