<?php

namespace App\Modules\Attendance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'user_id',
        'attendance_date',
        'check_in_at',
        'check_in_photo_path',
        'check_in_latitude',
        'check_in_longitude',
		'check_in_address', // 👈 Tambahkan ini
        'check_in_accuracy_meters',
        'check_in_distance_meters',
        'check_in_location_status',
        'check_out_at',
        'check_out_photo_path',
        'check_out_latitude',
        'check_out_longitude',
		'check_out_address', // 👈 Dan ini
        'check_out_accuracy_meters',
        'check_out_distance_meters',
        'check_out_location_status',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'check_in_latitude' => 'float',
            'check_in_longitude' => 'float',
            'check_out_latitude' => 'float',
            'check_out_longitude' => 'float',
            'check_in_distance_meters' => 'integer',
            'check_out_distance_meters' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}