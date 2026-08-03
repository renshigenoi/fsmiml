<?php

namespace App\Modules\Tracking\Models;

use App\Modules\Assignment\Models\Assignment;
use App\Modules\Tracking\Enums\TrackingSessionStatus;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrackingSession extends Model
{
    protected $fillable = [
        'work_order_id',
        'assignment_id',
        'status',
        'started_at',
        'ended_at',
        'closed_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => TrackingSessionStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(TrackingPoint::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(TrackingToken::class);
    }
}
