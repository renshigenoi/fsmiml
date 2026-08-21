<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class FinishWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photos' => ['required', 'array', 'min:1', 'max:5'],
            'photos.*' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
            'note' => ['nullable', 'string', 'max:5000'],
            'processed_at' => ['nullable', 'date'],
            'sync_token' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'photos.required' => 'Minimal 1 foto pemasangan wajib dilampirkan.',
            'photos.min' => 'Minimal 1 foto pemasangan wajib dilampirkan.',
            'photos.max' => 'Maksimal 5 foto yang bisa diunggah.',
            'photos.*.image' => 'File harus berupa gambar.',
            'photos.*.max' => 'Ukuran tiap foto maksimal 8 MB.',
        ];
    }
}
