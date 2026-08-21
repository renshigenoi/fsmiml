<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\FinishWorkOrderRequest;
use App\Http\Requests\Api\V1\StartInstallationRequest;
use App\Http\Requests\Api\V1\ReasonRequest;
use App\Http\Requests\Api\V1\StoreWorkOrderRequest;
use App\Http\Resources\Api\V1\WorkOrderResource;
use App\Models\User;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\Legacy\Services\LegacyDataSourceService;
use App\Modules\Legacy\Support\LegacyRowFormatter;
use App\Modules\Legacy\Support\SalesDetailMapper;
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

    public function show(WorkOrder $workOrder, LegacyDataSourceService $legacy): WorkOrderResource
    {
        $this->authorize('view', $workOrder);

        $workOrder->load([
            'customer',
            'serviceLocation',
            'items',
            'salesOrder',
            'assignments',
            'statusHistories',
            'trackingSessions',
            'photos',
        ]);

        $payload = $workOrder->salesOrder?->source_payload ?? [];

        $workOrder->car_info = [
            'brand' => $payload['car_brand'] ?? null,
            'model' => $payload['car_model'] ?? null,
            'chassis_no' => $payload['chassis_no'] ?? null,
            'police_no' => $payload['police_no'] ?? null,
            'installation_type' => SalesDetailMapper::installationTypeLabel(
                $payload['installation_type'] ?? null,
            ),
            'sales_type' => $payload['sales_type'] ?? null,
        ];

        $workOrder->sales_details = ($salesSerial = $workOrder->salesOrder?->external_id)
            ? array_map(
                fn (object $row): array => LegacyRowFormatter::salesDetail($row),
                $legacy->salesDetails($salesSerial),
            )
            : [];

        return new WorkOrderResource($workOrder);
    }

    public function startTrip(WorkOrder $workOrder, WorkOrderTransitionService $transitions): WorkOrderResource
    {
        return $this->transition($workOrder, WorkOrderStatus::OnTheWay, $transitions);
    }

    public function arrive(WorkOrder $workOrder, WorkOrderTransitionService $transitions): WorkOrderResource
    {
        return $this->transition($workOrder, WorkOrderStatus::Arrived, $transitions);
    }

    public function startInstallation(StartInstallationRequest $request, WorkOrder $workOrder, WorkOrderTransitionService $transitions): WorkOrderResource
    {
        /** @var User $actor */
        $actor = $request->user();
        $this->authorize('view', $workOrder);

        if ($request->filled('sync_token') && $workOrder->photos()
            ->where('sync_token', (string) $request->string('sync_token'))
            ->exists()) {
            return new WorkOrderResource($workOrder->load('photos'));
        }

        $result = DB::transaction(function () use ($request, $workOrder, $transitions, $actor): WorkOrder {
            if ($request->filled('note')) {
                $workOrder->installation_note = $request->input('note');
                $workOrder->save();
            }

            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('work-orders/' . $workOrder->id . '/before-installation', 'public');

                $workOrder->photos()->create([
                    'uploaded_by' => $actor->id,
                    'stage' => 'before_installation',
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $photo->getClientOriginalName(),
                    'mime_type' => $photo->getMimeType(),
                    'size_bytes' => $photo->getSize(),
                    'captured_at' => $request->date('processed_at') ?? now(),
                    'sync_token' => $request->input('sync_token'),
                ]);
            }

            return $transitions->transition($workOrder, WorkOrderStatus::Installation, $actor);
        });

        return new WorkOrderResource($result->load('photos'));
    }

    public function finish(FinishWorkOrderRequest $request, WorkOrder $workOrder, WorkOrderTransitionService $transitions): WorkOrderResource
    {
        /** @var User $actor */
        $actor = $request->user();
        $this->authorize('view', $workOrder);

        if ($request->filled('sync_token') && $workOrder->photos()
            ->where('sync_token', (string) $request->string('sync_token'))
            ->exists()) {
            return new WorkOrderResource($workOrder->load('photos'));
        }

        $result = DB::transaction(function () use ($request, $workOrder, $transitions, $actor): WorkOrder {
            if ($request->filled('note')) {
                $workOrder->completion_note = $request->input('note');
                $workOrder->save();
            }

            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('work-orders/' . $workOrder->id . '/completion', 'public');

                $workOrder->photos()->create([
                    'uploaded_by' => $actor->id,
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $photo->getClientOriginalName(),
                    'mime_type' => $photo->getMimeType(),
                    'size_bytes' => $photo->getSize(),
                    'captured_at' => $request->date('processed_at') ?? now(),
                    'sync_token' => $request->input('sync_token'),
                ]);
            }

            return $transitions->transition($workOrder, WorkOrderStatus::Finished, $actor);
        });

        return new WorkOrderResource($result->load('photos'));
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
