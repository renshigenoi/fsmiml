<?php

namespace App\Modules\Identity\Models;

use App\Models\User;
use App\Modules\Assignment\Models\Assignment;
use App\Modules\Attendance\Models\WorkLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Technician extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'work_location_id',
        'employee_code',
        'external_serial',
        'phone',
        'is_active',
        'attendance_mode',
        'attendance_radius_override',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function workLocation(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class);
    }
}
