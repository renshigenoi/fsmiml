<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetUserPin extends Command
{
    protected $signature = 'fsm:reset-pin {email} {pin}';

    protected $description = 'Reset PIN aplikasi mobile untuk akun tertentu.';

    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if ($user === null) {
            $this->error('Akun tidak ditemukan.');

            return self::FAILURE;
        }

        $user->update(['pin_hash' => Hash::make($this->argument('pin'))]);

        $this->info("PIN akun {$user->email} berhasil direset.");

        return self::SUCCESS;
    }
}
