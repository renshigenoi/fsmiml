<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AssignWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'technician_ids' => ['required', 'array', 'min:1', 'max:20'],
            'technician_ids.*' => ['required', 'integer', 'distinct', 'exists:technicians,id'],
        ];
    }
}
