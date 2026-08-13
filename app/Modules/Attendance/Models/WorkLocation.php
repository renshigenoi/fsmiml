<?php

namespace App\Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkLocation extends Model
{
    protected $fillable = ['name', 'address', 'latitude', 'longitude', 'radius_meters', 'is_active'];

    protected function casts(): array
    {
        return ['latitude' => 'float', 'longitude' => 'float', 'is_active' => 'boolean'];
    }

    public function technicians(): HasMany
    {
        return $this->hasMany(\App\Modules\Identity\Models\Technician::class);
    }
}
