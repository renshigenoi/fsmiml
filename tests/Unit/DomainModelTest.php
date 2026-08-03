<?php

namespace Tests\Unit;

use App\Modules\Assignment\Events\AssignmentCreated;
use App\Modules\Assignment\Models\Assignment;
use App\Modules\Customer\Models\Customer;
use App\Modules\Tracking\Models\TrackingSession;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;
use App\Modules\WorkOrder\Events\WorkOrderStatusChanged;
use App\Modules\WorkOrder\Models\WorkOrder;
use App\Modules\WorkOrder\Services\WorkOrderTransitionService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class DomainModelTest extends TestCase
{
    public function test_work_order_uses_status_enum_and_exposes_its_domain_relations(): void
    {
        $workOrder = new WorkOrder;

        $this->assertSame(WorkOrderStatus::class, $workOrder->getCasts()['status']);
        $this->assertInstanceOf(BelongsTo::class, $workOrder->customer());
        $this->assertInstanceOf(HasMany::class, $workOrder->assignments());
        $this->assertInstanceOf(HasMany::class, $workOrder->trackingSessions());
    }

    public function test_cross_module_relationships_use_the_expected_models(): void
    {
        $customer = new Customer;
        $assignment = new Assignment;
        $trackingSession = new TrackingSession;

        $this->assertSame(WorkOrder::class, $customer->workOrders()->getRelated()::class);
        $this->assertSame(WorkOrder::class, $assignment->workOrder()->getRelated()::class);
        $this->assertSame(Assignment::class, $trackingSession->assignment()->getRelated()::class);
    }

    public function test_state_machine_allows_only_documented_work_order_transitions(): void
    {
        $this->assertTrue(WorkOrderTransitionService::canTransition(WorkOrderStatus::Accepted, WorkOrderStatus::OnTheWay));
        $this->assertTrue(WorkOrderTransitionService::canTransition(WorkOrderStatus::Installation, WorkOrderStatus::Finished));
        $this->assertFalse(WorkOrderTransitionService::canTransition(WorkOrderStatus::Draft, WorkOrderStatus::Finished));
        $this->assertFalse(WorkOrderTransitionService::canTransition(WorkOrderStatus::Finished, WorkOrderStatus::Accepted));
    }

    public function test_domain_events_are_autoloadable(): void
    {
        $this->assertTrue(class_exists(AssignmentCreated::class));
        $this->assertTrue(class_exists(WorkOrderStatusChanged::class));
    }
}
