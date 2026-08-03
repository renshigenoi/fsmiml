<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\Legacy\Services\LegacyDataSourceService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LegacyAccessTest extends TestCase
{
    #[Test]
    public function legacy_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/legacy/technicians')->assertUnauthorized();
        $this->getJson('/api/v1/legacy/sales')->assertUnauthorized();
    }

    #[Test]
    public function technicians_cannot_access_legacy_data(): void
    {
        $technician = new User(['name' => 'Teknisi', 'role' => UserRole::Technician]);

        $this->actingAs($technician)
            ->getJson('/api/v1/legacy/technicians')
            ->assertForbidden();

        $this->actingAs($technician)
            ->postJson('/api/v1/legacy/work-orders', [
                'legacy_sales_serial' => '4782f2a21674ad566ff0ff3fc243b9da',
                'technician_legacy_serials' => ['4a1b2c3d4e5f60718293a4b5c6d7e8f9'],
            ])
            ->assertForbidden();
    }

    #[Test]
    public function coordinator_can_search_legacy_sales(): void
    {
        $legacy = Mockery::mock(LegacyDataSourceService::class);
        $legacy->shouldReceive('sales')
            ->once()
            ->with('SPK-001', 100)
            ->andReturn([
                (object) [
                    'spk_no' => 'SPK-001',
                    'serial' => 99,
                    'sales_type' => '1',
                    'status' => '2',
                    'customer_name' => 'Budi Santoso',
                    'city' => 'Jakarta',
                ],
            ]);

        $this->app->instance(LegacyDataSourceService::class, $legacy);

        $coordinator = new User(['name' => 'Koordinator', 'role' => UserRole::Coordinator]);

        $this->actingAs($coordinator)
            ->getJson('/api/v1/legacy/sales?search=SPK-001')
            ->assertOk()
            ->assertJsonPath('data.0.spk_no', 'SPK-001')
            ->assertJsonPath('data.0.customer_name', 'Budi Santoso');
    }

    #[Test]
    public function creating_a_legacy_work_order_requires_the_expected_payload(): void
    {
        $coordinator = new User(['name' => 'Koordinator', 'role' => UserRole::Coordinator]);

        $this->actingAs($coordinator)
            ->postJson('/api/v1/legacy/work-orders', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['legacy_sales_serial', 'technician_legacy_serials']);
    }
}
