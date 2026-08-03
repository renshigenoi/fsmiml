<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ReasonRequest;
use App\Http\Requests\Api\V1\StoreWorkOrderRequest;
use App\Http\Resources\Api\V1\WorkOrderResource;
use App\Models\User;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;
use App\Modules\WorkOrder\Models\WorkOrder;
use App\Modules\WorkOrder\Models\WorkOrderStatusHistory;
use App\Modules\WorkOrder\Services\WorkOrderTransitionService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkOrderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = request()->user();
        $this->authorize('viewAny', WorkOrder::class);

        $query = WorkOrder::query()->with(['customer', 'serviceLocation', 'items', 'assignments']);

        if (! in_array($user->role, [UserRole::Administrator, UserRole::Coordinator], true)) {
            $query->whereHas('assignments', fn ($assignments) => $assignments->where('technician_id', $user->technician?->getKey()));
        }

        return WorkOrderResource::collection($query->latest('scheduled_start_at')->paginate());
    }

    public function store(StoreWorkOrderRequest $request): WorkOrderResource
    {
        /** @var User $actor */
        $actor = $request->user();
        $data = $request->validated();

        $workOrder = DB::transaction(function () use ($actor, $data): WorkOrder {
            $locationMatchesCustomer = DB::table('service_locations')
                ->where('id', $data['service_location_id'])
                ->where('customer_id', $data['customer_id'])
                ->exists();

            if (! $locationMatchesCustomer) {
                throw ValidationException::withMessages([
                    'service_location_id' => 'The service location must belong to the selected customer.',
                ]);
            }

            $workOrder = WorkOrder::query()->create([
                ...collect($data)->except('items')->all(),
                'status' => WorkOrderStatus::Draft,
                'created_by' => $actor->getKey(),
            ]);

            $workOrder->items()->createMany($data['items']);

            WorkOrderStatusHistory::query()->create([
                'work_order_id' => $workOrder->getKey(),
                'from_status' => null,
                'to_status' => WorkOrderStatus::Draft,
                'actor_user_id' => $actor->getKey(),
                'metadata' => ['source' => 'api'],
                'occurred_at' => now(),
            ]);

            return $workOrder;
        });

        return new WorkOrderResource($workOrder->load('items'));
    }

    public function show(WorkOrder $workOrder): WorkOrderResource
    {
        $this->authorize('view', $workOrder);

        return new WorkOrderResource($workOrder->load([
            'customer',
            'serviceLocation',
            'items',
            'assignments',
            'statusHistories',
            'trackingSessions',
        ]));
    }

    public function startTrip(WorkOrder $workOrder, WorkOrderTransitionService $transitions): WorkOrderResource
    {
        return $this->transition($workOrder, WorkOrderStatus::OnTheWay, $transitions);
    }

    public function arrive(WorkOrder $workOrder, WorkOrderTransitionService $transitions): WorkOrderResource
    {
        return $this->transition($workOrder, WorkOrderStatus::Arrived, $transitions);
    }

    public function startInstallation(WorkOrder $workOrder, WorkOrderTransitionService $transitions): WorkOrderResource
    {
        return $this->transition($workOrder, WorkOrderStatus::Installation, $transitions);
    }

    public function finish(WorkOrder $workOrder, WorkOrderTransitionService $transitions): WorkOrderResource
    {
        return $this->transition($workOrder, WorkOrderStatus::Finished, $transitions);
    }

    public function cancel(ReasonRequest $request, WorkOrder $workOrder, WorkOrderTransitionService $transitions): WorkOrderResource
    {
        $this->authorize('cancel', $workOrder);

        return $this->transition($workOrder, WorkOrderStatus::Cancelled, $transitions, $request->validated('reason'));
    }

    public function fail(ReasonRequest $request, WorkOrder $workOrder, WorkOrderTransitionService $transitions): WorkOrderResource
    {
        return $this->transition($workOrder, WorkOrderStatus::Failed, $transitions, $request->validated('reason'));
    }

    private function transition(
        WorkOrder $workOrder,
        WorkOrderStatus $status,
        WorkOrderTransitionService $transitions,
        ?string $reason = null,
    ): WorkOrderResource {
        /** @var User $actor */
        $actor = request()->user();
        $this->authorize('view', $workOrder);

        return new WorkOrderResource($transitions->transition($workOrder, $status, $actor, $reason));
    }
}
