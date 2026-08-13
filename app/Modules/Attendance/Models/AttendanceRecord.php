<?php

namespace App\Modules\Attendance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = ['user_id', 'attendance_date'];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date', 'check_in_at' => 'datetime', 'check_out_at' => 'datetime',
            'check_in_latitude' => 'float', 'check_in_longitude' => 'float',
            'check_out_latitude' => 'float', 'check_out_longitude' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
