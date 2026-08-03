<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Modules\Identity\Enums\UserRole;
use Tests\TestCase;

class V1ApiTest extends TestCase
{
    public function test_work_order_endpoints_require_sanctum_authentication(): void
    {
        $this->getJson('/api/v1/work-orders')
            ->assertUnauthorized();
    }

    public function test_login_request_requires_credentials(): void
    {
        $this->postJson('/api/v1/auth/login')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_change_password_endpoint_requires_authentication(): void
    {
        $this->postJson('/api/v1/auth/change-password')
            ->assertUnauthorized();
    }

    public function test_change_password_rejects_mismatched_confirmation(): void
    {
        $user = new User(['name' => 'Teknisi', 'role' => UserRole::Technician]);

        $this->actingAs($user)
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => '12345',
                'new_password' => 'rahasia123',
                'new_password_confirmation' => 'bedalain',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('new_password');
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        $user = new User(['name' => 'Teknisi', 'role' => UserRole::Technician]);

        $this->actingAs($user)
            ->postJson('/api/v1/auth/change-password', [
                'current_password' => 'salah',
                'new_password' => 'rahasia123',
                'new_password_confirmation' => 'rahasia123',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');
    }
}
