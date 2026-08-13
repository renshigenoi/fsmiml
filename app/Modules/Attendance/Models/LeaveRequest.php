<?php

namespace App\Modules\Attendance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = ['user_id', 'type', 'leave_date', 'note', 'status'];

    protected function casts(): array
    {
        return ['leave_date' => 'date', 'reviewed_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
