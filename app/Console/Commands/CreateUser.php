<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Identity\Enums\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    protected $signature = 'fsm:create-user {name} {email} {password} {--role=coordinator}';

    protected $description = 'Create an FSM user (roles: administrator, coordinator, technician)';

    public function handle(): int
    {
        $role = UserRole::tryFrom($this->option('role'));

        if ($role === null) {
            $this->error('Role tidak valid. Gunakan: administrator, coordinator, atau technician.');

            return self::FAILURE;
        }

        $email = strtolower($this->argument('email'));

        if (User::query()->where('email', $email)->exists()) {
            $this->error("Email {$email} sudah terdaftar.");

            return self::FAILURE;
        }

        User::query()->create([
            'name' => $this->argument('name'),
            'email' => $email,
            'password' => Hash::make($this->argument('password')),
            'role' => $role,
        ]);

        $this->info("User dibuat: {$email} (role: {$role->value}).");

        return self::SUCCESS;
    }
}
