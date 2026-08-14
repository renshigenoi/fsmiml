<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\Identity\Models\Technician;
use App\Modules\Legacy\Services\LegacyDataSourceService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BonusTest extends TestCase
{
    #[Test]
    public function technician_only_receives_own_legacy_bonus_for_selected_date(): void
    {
        $legacy = Mockery::mock(LegacyDataSourceService::class);
        $legacy->shouldReceive('bonusesForTechnician')
            ->once()
            ->with('legacy-tech-7', '2026-08-14')
            ->andReturn([
                (object) [
                    'teknisi_name' => 'Teknisi Tujuh',
                    'date' => '2026-08-14',
                    'sales_invoice_no_car' => null,
                    'sales_invoice_no_building' => 'INV-BUILDING-1',
                    'sales_invoice_no_materials' => 'INV-MATERIAL-1',
                    'total' => '150000',
                ],
            ]);
        $this->app->instance(LegacyDataSourceService::class, $legacy);

        $user = new User(['name' => 'Teknisi', 'role' => UserRole::Technician]);
        $user->setRelation('technician', new Technician(['external_serial' => 'legacy-tech-7']));

        $this->actingAs($user)
            ->getJson('/api/v1/bonuses?date=2026-08-14')
            ->assertOk()
            ->assertJsonPath('data.0.technician_name', 'Teknisi Tujuh')
            ->assertJsonPath('data.0.invoice_number', 'INV-BUILDING-1')
            ->assertJsonPath('data.0.total', 150000)
            ->assertJsonPath('meta.total_bonus', 150000);
    }
}
