<?php

namespace App\Modules\Sales\Models;

use App\Modules\Customer\Models\Customer;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    protected $fillable = [
        'external_id',
        'invoice_number',
        'customer_id',
        'status',
        'ordered_at',
        'source_payload',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'ordered_at' => 'datetime',
            'source_payload' => 'array',
            'synced_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }
}
