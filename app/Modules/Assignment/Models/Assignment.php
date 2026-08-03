<?php

namespace App\Modules\Assignment\Models;

use App\Models\User;
use App\Modules\Assignment\Enums\AssignmentStatus;
use App\Modules\Identity\Models\Technician;
use App\Modules\Tracking\Models\TrackingSession;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    protected $fillable = [
        'work_order_id',
        'technician_id',
        'status',
        'assigned_by',
        'assigned_at',
        'responded_at',
        'rejected_reason',
        'superseded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AssignmentStatus::class,
            'assigned_at' => 'datetime',
            'responded_at' => 'datetime',
            'superseded_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function trackingSessions(): HasMany
    {
        return $this->hasMany(TrackingSession::class);
    }
}
