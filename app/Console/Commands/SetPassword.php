<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Identity\Models\Technician;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetPassword extends Command
{
    protected $signature = 'fsm:set-password {identifier} {password}';

    protected $description = 'Set/reset password akun: bisa pakai email, kode pegawai (user_id), serial, atau nomor HP teknisi';

    public function handle(): int
    {
        $identifier = trim((string) $this->argument('identifier'));
        $password = (string) $this->argument('password');

        if (strlen($password) < 6) {
            $this->error('Password minimal 6 karakter.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $identifier)->first();

        if ($user === null) {
            $technician = Technician::query()
                ->where('employee_code', $identifier)
                ->orWhere('external_serial', $identifier)
                ->orWhere('phone', $identifier)
                ->first();

            $user = $technician?->user;
        }

        if ($user === null) {
            $this->error("Akun tidak ditemukan untuk: {$identifier}");
            $this->line('Coba pakai email, kode pegawai (user_id), serial, atau nomor HP teknisi.');

            return self::FAILURE;
        }

        $user->update(['password' => Hash::make($password)]);

        $this->info("Password berhasil diubah: {$user->email} ({$user->name})");

        return self::SUCCESS;
    }
}
