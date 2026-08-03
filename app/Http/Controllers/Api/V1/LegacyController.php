<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreLegacyWorkOrderRequest;
use App\Http\Resources\Api\V1\WorkOrderResource;
use App\Models\User;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\Legacy\Services\LegacyDataSourceService;
use App\Modules\Legacy\Services\LegacyWorkOrderService;
use App\Modules\Legacy\Support\LegacyRowFormatter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LegacyController extends Controller
{
    public function technicians(Request $request, LegacyDataSourceService $legacy): JsonResponse
    {
        $this->authorizeLegacyAccess($request->user());

        $rows = $legacy->technicians(
            $request->query('search'),
            (int) $request->query('limit', 200),
        );

        return response()->json([
            'data' => array_map(fn (object $row): array => LegacyRowFormatter::technician($row), $rows),
        ]);
    }

    public function sales(Request $request, LegacyDataSourceService $legacy): JsonResponse
    {
        $this->authorizeLegacyAccess($request->user());

        $rows = $legacy->sales(
            $request->query('search'),
            (int) $request->query('limit', 100),
        );

        return response()->json([
            'data' => array_map(fn (object $row): array => LegacyRowFormatter::sales($row), $rows),
        ]);
    }

    public function storeWorkOrder(
        StoreLegacyWorkOrderRequest $request,
        LegacyWorkOrderService $workOrders,
    ): JsonResponse {
        $this->authorizeLegacyAccess($request->user());

        $validated = $request->validated();
        $workOrder = $workOrders->createFromSales(
            $validated['legacy_sales_serial'],
            $validated['technician_legacy_serials'],
            $validated['scheduled_start_at'] ?? null,
            $validated['notes'] ?? null,
            $request->user(),
        );

        return response()->json([
            'message' => 'Work order created and assigned.',
            'work_order' => new WorkOrderResource($workOrder->load([
                'customer',
                'serviceLocation',
                'items',
                'assignments',
            ])),
        ], 201);
    }

    private function authorizeLegacyAccess(User $user): void
    {
        if (! in_array($user->role, [UserRole::Administrator, UserRole::Coordinator], true)) {
            throw new AuthorizationException('Only coordinators may access legacy sales data.');
        }
    }
}
