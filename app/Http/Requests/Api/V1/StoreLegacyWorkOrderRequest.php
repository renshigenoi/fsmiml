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
            'scheduled_start_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
