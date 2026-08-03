<?php

namespace Tests\Feature\Api;

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
}
