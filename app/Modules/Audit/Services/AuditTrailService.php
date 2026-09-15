<?php

namespace App\Modules\Audit\Services;

use App\Models\User;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Mencatat jejak audit (siapa melakukan apa, ke objek mana, kapan) untuk
 * aksi-aksi administratif & sensitif sesuai FR-AUTH-005.
 */
class AuditTrailService
{
    public function record(
        string $action,
        ?Model $subject = null,
        array $properties = [],
        ?User $actor = null,
    ): AuditLog {
        $actor ??= User::query()->find(auth()->id());

        return AuditLog::query()->create([
            'user_id' => $actor?->getKey(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties === [] ? null : $properties,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
