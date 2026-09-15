<?php

namespace App\Modules\Attendance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = ['user_id', 'type', 'leave_date', 'leave_end_date', 'start_time', 'end_time', 'note', 'status', 'reviewed_by', 'review_note', 'reviewed_at'];

    protected function casts(): array
    {
        return ['leave_date' => 'date', 'leave_end_date' => 'date', 'reviewed_at' => 'datetime', 'start_time' => 'datetime:H:i', 'end_time' => 'datetime:H:i'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
