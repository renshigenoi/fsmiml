<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreLegacyWorkOrderRequest;
use App\Modules\Assignment\Exceptions\InvalidAssignment;
use App\Modules\Legacy\Services\LegacyDataSourceService;
use App\Modules\Legacy\Services\LegacyWorkOrderService;
use App\Modules\Legacy\Support\LegacyRowFormatter;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly LegacyDataSourceService $legacy,
        private readonly LegacyWorkOrderService $workOrders,
    ) {}

    public function index(): View
    {
        $statusCounts = WorkOrder::query()
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pending = WorkOrder::query()
            ->with(['customer', 'assignments.technician.user'])
            ->where('status', WorkOrderStatus::WaitingAcceptance)
            ->latest('scheduled_start_at')
            ->limit(8)
            ->get();

        $technicians = collect($this->legacy->technicians(null, 8));

        return view('dashboard.home', [
            'statusCounts' => $statusCounts,
            'pending' => $pending,
            'technicians' => $technicians,
            'technicianCount' => $this->legacy->countTechnicians(),
        ]);
    }

    public function input(): View
    {
        return view('dashboard.input');
    }

    public function workOrders(Request $request): View
    {
        $statusParam = $request->query('status');

        $selectedStatus = match (true) {
            $statusParam === null => WorkOrderStatus::WaitingAcceptance,
            $statusParam === 'all' => null,
            default => WorkOrderStatus::tryFrom((string) $statusParam) ?? WorkOrderStatus::WaitingAcceptance,
        };

        $workOrders = WorkOrder::query()
            ->with(['customer', 'assignments.technician.user'])
            ->when($selectedStatus !== null, fn ($query) => $query->where('status', $selectedStatus))
            ->latest('scheduled_start_at')
            ->paginate(20);

        return view('dashboard.work-orders', [
            'workOrders' => $workOrders,
            'selectedStatus' => $selectedStatus?->value,
            'statuses' => WorkOrderStatus::cases(),
        ]);
    }

    public function technicians(Request $request): View
    {
        $rows = $this->legacy->technicians($request->query('search'), 200);

        return view('dashboard.technicians', [
            'technicians' => $rows,
            'search' => (string) $request->query('search'),
        ]);
    }

    public function searchSales(Request $request): JsonResponse
    {
        $rows = $this->legacy->sales($request->query('search'), 10);

        return response()
            ->json([
                'data' => array_map(fn (object $row): array => LegacyRowFormatter::sales($row), $rows),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function techniciansJson(Request $request): JsonResponse
    {
        $rows = $this->legacy->technicians($request->query('search'), 200);

        return response()
            ->json([
                'data' => array_map(fn (object $row): array => LegacyRowFormatter::technician($row), $rows),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function storeWorkOrder(StoreLegacyWorkOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $workOrder = $this->workOrders->createFromSales(
                $validated['legacy_sales_serial'],
                $validated['technician_legacy_serials'],
                $validated['scheduled_start_at'] ?? null,
                $validated['notes'] ?? null,
                $request->user(),
            );
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        } catch (InvalidAssignment $exception) {
            Log::warning('Dashboard work order assignment failed.', [
                'error' => $exception->getMessage(),
            ]);

            return back()->with('error', $exception->getMessage())->withInput();
        }

        return redirect()->route('dashboard')->with('success', sprintf(
            'Work Order %s berhasil dibuat dan ditugaskan ke %d teknisi.',
            $workOrder->number,
            $workOrder->assignments()->count(),
        ));
    }

    public function showWorkOrder(WorkOrder $workOrder): View
    {
        $workOrder->load([
            'customer',
            'serviceLocation',
            'items',
            'assignments.technician.user',
            'statusHistories',
            'trackingSessions',
        ]);

        return view('dashboard.work-order', ['workOrder' => $workOrder]);
    }
}
