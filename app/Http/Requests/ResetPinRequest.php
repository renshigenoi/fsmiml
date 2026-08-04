<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:190'],
            'pin' => ['required', 'digits:6'],
            'pin_confirmation' => ['required', 'same:pin'],
        ];
    }
}
