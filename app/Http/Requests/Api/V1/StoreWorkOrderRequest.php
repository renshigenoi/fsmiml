<?php

namespace App\Http\Requests\Api\V1;

use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', WorkOrder::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'max:50', 'unique:work_orders,number'],
            'sales_order_id' => ['nullable', 'integer', 'exists:sales_orders,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'service_location_id' => ['required', 'integer', 'exists:service_locations,id'],
            'work_type' => ['required', 'string', 'max:50'],
            'scheduled_start_at' => ['required', 'date'],
            'scheduled_end_at' => ['nullable', 'date', 'after:scheduled_start_at'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_order_item_id' => ['nullable', 'integer', 'exists:sales_order_items,id'],
            'items.*.product_code' => ['nullable', 'string', 'max:100'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit' => ['nullable', 'string', 'max:30'],
        ];
    }
}
