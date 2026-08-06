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
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:190'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'technician_legacy_serials.required' => 'Pilih minimal 1 teknisi untuk assignment.',
            'technician_legacy_serials.min' => 'Pilih minimal 1 teknisi.',
            'latitude.required' => 'Lokasi pemasangan wajib di-pin di peta. Klik pada peta untuk menentukan titik lokasi.',
            'longitude.required' => 'Lokasi pemasangan wajib di-pin di peta. Klik pada peta untuk menentukan titik lokasi.',
            'customer_phone.required' => 'No. WhatsApp customer wajib diisi — diperlukan untuk notifikasi live tracking ke customer.',
        ];
    }
}
