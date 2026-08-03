<?php

namespace App\Modules\Mobile\Models;

use Illuminate\Database\Eloquent\Model;

class MobileAppRelease extends Model
{
    protected $fillable = [
        'platform',
        'version_code',
        'version_name',
        'minimum_version_code',
        'is_mandatory',
        'apk_url',
        'sha256',
        'release_notes',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'version_code' => 'integer',
            'minimum_version_code' => 'integer',
            'is_mandatory' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
