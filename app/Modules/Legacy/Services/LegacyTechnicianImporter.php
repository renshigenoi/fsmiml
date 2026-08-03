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

        foreach (array_values(array_unique($serials)) as $serial) {
            $row = $this->legacy->technicianBySerial((string) $serial);

            if ($row === null) {
                continue;
            }

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
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'phone' => $phone,
                    'password' => Hash::make(config('fsm.technician_default_password')),
                    'role' => UserRole::Technician,
                ],
            );

            return Technician::query()->create([
                'user_id' => $user->getKey(),
                'employee_code' => $this->uniqueEmployeeCode($row->user_id ?: 'LEG-'.$serial),
                'external_serial' => $serial,
                'phone' => $phone,
                'is_active' => true,
            ]);
        }

        $technician->user()->update(['name' => $name, 'phone' => $phone]);
        $technician->update(['phone' => $phone, 'is_active' => true]);

        return $technician;
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
