<?php

namespace App\Modules\Audit\Services;

use App\Models\User;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

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
    ): ?AuditLog {
        try {
            $actor ??= auth()->check() ? User::query()->find(auth()->id()) : null;

            return AuditLog::query()->create([
                'user_id' => $actor?->getKey(),
                'action' => $action,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'properties' => $properties === [] ? null : $properties,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            // C4: kegagalan menulis audit TIDAK boleh membatalkan/meng-500-kan
            // aksi utama yang sudah terjadi (mis. PIN sudah terlanjur diganti).
            Log::error('Audit trail gagal ditulis.', [
                'action' => $action,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
