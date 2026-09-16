<?php

namespace App\Modules\Legacy\Services;

use App\Models\User;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\Identity\Models\Technician;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

/**
 * Copies legacy technicians into the FSM identity tables (users + technicians)
 * so assignments can reference local technician rows. Deduplicated by the
 * legacy `serial` through the new `technicians.external_serial` column.
 */
class LegacyTechnicianImporter
{
    public function __construct(private readonly LegacyDataSourceService $legacy) {}

    /**
     * @param  array<int, string>  $serials
     * @return Collection<int, Technician>
     */
    public function importBySerials(array $serials): Collection
    {
        $technicians = new Collection;
        $uniqueSerials = array_values(array_unique($serials));

        if ($uniqueSerials === []) {
            return $technicians;
        }

        // Batch fetch: 1 query ke DB lama untuk semua serial,
        // lalu upsert satu per satu ke DB FSM.
        $rows = $this->legacy->techniciansBySerials($uniqueSerials);

        foreach ($rows as $row) {
            $technicians->push($this->upsert($row));
        }

        return $technicians;
    }

    private function upsert(object $row): Technician
    {
        $serial = (string) $row->serial;
        $name = filled($row->full_name) ? $row->full_name : "Teknisi {$serial}";
        $phone = $row->cell_phone ?: $row->home_phone;
        $email = filled($row->email) ? $row->email : "tech.{$serial}@legacy.fsm.local";

        $technician = Technician::query()->where('external_serial', $serial)->first();

        if ($technician === null) {
            $user = $this->resolveUser($email, $name, $phone);

            return Technician::query()->create([
                'user_id' => $user->getKey(),
                'employee_code' => $this->uniqueEmployeeCode($row->user_id ?: 'LEG-'.$serial),
                'external_serial' => $serial,
                'phone' => $phone,
                'is_active' => true,
            ]);
        }

        // A7: akun lama bisa sudah di-soft-delete (relasi `user` mengembalikan
        // null karena global scope) — resolveUser memulihkannya, alih-alih
        // no-op senyap yang mengaktifkan teknisi tanpa user.
        $user = $technician->user ?? $this->resolveUser($email, $name, $phone);

        $user->update(['name' => $name, 'phone' => $phone]);
        $technician->update([
            'user_id' => $user->getKey(),
            'phone' => $phone,
            'is_active' => true,
        ]);

        return $technician;
    }

    /**
     * Cari user by email TERMASUK soft-deleted (unique index email tetap
     * memandang baris terhapus — firstOrCreate polos memicu SQLSTATE 23505),
     * pulihkan bila terhapus, selain itu buat baru.
     */
    private function resolveUser(string $email, string $name, ?string $phone): User
    {
        $user = User::query()->withTrashed()->where('email', $email)->first();

        if ($user === null) {
            return User::query()->create([
                'email' => $email,
                'name' => $name,
                'phone' => $phone,
                'password' => Hash::make(config('fsm.technician_default_password')),
                'role' => UserRole::Technician,
            ]);
        }

        if ($user->trashed()) {
            $user->restore();
        }

        return $user;
    }

    private function uniqueEmployeeCode(string $code): string
    {
        if (! Technician::query()->where('employee_code', $code)->exists()) {
            return $code;
        }

        $candidate = $code;
        $suffix = 2;

        while (Technician::query()->where('employee_code', $candidate)->exists()) {
            $candidate = $code.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
