<?php

namespace App\Models;

use App\Modules\Assignment\Models\Assignment;
use App\Modules\Attendance\Models\AttendanceRecord;
use App\Modules\Attendance\Models\LeaveRequest;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\Identity\Models\Technician;
use App\Modules\Identity\Models\UserDeviceToken;
use App\Modules\Notification\Models\Notification;
use App\Modules\WorkOrder\Models\WorkOrder;
use App\Modules\WorkOrder\Models\WorkOrderStatusHistory;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'pin_hash',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function technician(): HasOne
    {
        return $this->hasOne(Technician::class);
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(UserDeviceToken::class);
    }

    public function createdWorkOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'created_by');
    }

    public function assignedWorkOrders(): HasMany
    {
        return $this->hasMany(Assignment::class, 'assigned_by');
    }

    public function workOrderStatusHistories(): HasMany
    {
        return $this->hasMany(WorkOrderStatusHistory::class, 'actor_user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function attendanceRecords(): HasMany { return $this->hasMany(AttendanceRecord::class); }
    public function leaveRequests(): HasMany { return $this->hasMany(LeaveRequest::class); }
}
