<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * B12: request minimal untuk endpoint transisi tanpa body wajib
 * (startTrip / arrive) — memvalidasi sync_token agar idempotency
 * tidak mati senyap saat klien salah kirim tipe data.
 */
class TransitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sync_token' => ['nullable', 'string', 'max:64'],
        ];
    }
}
