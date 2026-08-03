<?php

namespace App\Modules\WorkOrder\Models;

use App\Models\User;
use App\Modules\Assignment\Models\Assignment;
use App\Modules\Customer\Models\Customer;
use App\Modules\Customer\Models\ServiceLocation;
use App\Modules\Notification\Models\Notification;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Tracking\Models\TrackingSession;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'number',
        'sales_order_id',
        'customer_id',
        'service_location_id',
        'work_type',
        'status',
        'scheduled_start_at',
        'scheduled_end_at',
        'notes',
        'cancelled_reason',
        'failed_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => WorkOrderStatus::class,
            'scheduled_start_at' => 'datetime',
            'scheduled_end_at' => 'datetime',
        ];
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceLocation(): BelongsTo
    {
        return $this->belongsTo(ServiceLocation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(WorkOrderStatusHistory::class);
    }

    public function trackingSessions(): HasMany
    {
        return $this->hasMany(TrackingSession::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
