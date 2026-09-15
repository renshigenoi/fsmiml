<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SetPinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_pin' => ['nullable', 'digits:6'],
            'pin' => ['required', 'digits:6'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $user = $this->user();
            $hasExistingPin = filled($user->pin_hash);

            if (! $hasExistingPin) {
                return;
            }

            if (! $this->filled('current_pin') || ! \Illuminate\Support\Facades\Hash::check($this->input('current_pin'), (string) $user->pin_hash)) {
                $validator->errors()->add('current_pin', 'PIN saat ini tidak cocok.');
            }
        });
    }
}
