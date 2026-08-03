<?php

namespace Tests\Feature\Web;

use App\Models\User;
use App\Modules\Identity\Enums\UserRole;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    #[Test]
    public function profile_requires_login(): void
    {
        $this->get('/dashboard/profile')->assertRedirect('/login');
    }

    #[Test]
    public function profile_rejects_mismatched_password_confirmation(): void
    {
        $user = new User(['name' => 'Koordinator', 'role' => UserRole::Coordinator]);

        $this->actingAs($user)
            ->post('/dashboard/profile', [
                'name' => 'Koordinator',
                'password' => 'rahasia123',
                'password_confirmation' => 'bedalain123',
            ])
            ->assertSessionHasErrors(['password', 'current_password']);
    }

    #[Test]
    public function profile_rejects_wrong_current_password(): void
    {
        $user = new User(['name' => 'Koordinator', 'role' => UserRole::Coordinator]);

        $this->actingAs($user)
            ->post('/dashboard/profile', [
                'name' => 'Koordinator',
                'current_password' => 'salah',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
            ])
            ->assertSessionHasErrors('current_password');
    }
}
