<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreLegacyWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'legacy_sales_serial' => ['required', 'string', 'max:100'],
            'technician_legacy_serials' => ['required', 'array', 'min:1', 'max:20'],
            'technician_legacy_serials.*' => ['required', 'string', 'distinct', 'max:100'],
            'scheduled_start_at' => ['required', 'date'],
            'scheduled_start_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'location_address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:190'],
        ];
    }
}
