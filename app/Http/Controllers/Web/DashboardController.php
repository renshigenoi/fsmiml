<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPinRequest;
use App\Http\Requests\Api\V1\StoreLegacyWorkOrderRequest;
use App\Http\Requests\UpdateWorkOrderRequest;
use App\Models\User;
use App\Modules\Assignment\Exceptions\InvalidAssignment;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\Legacy\Services\LegacyDataSourceService;
use App\Modules\Legacy\Services\LegacyWorkOrderService;
use App\Modules\Legacy\Support\LegacyRowFormatter;
use App\Modules\Tracking\Enums\TrackingSessionStatus;
use App\Modules\Tracking\Enums\TrackingTokenStatus;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
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

        $single = null;
        $multi = [];
        $selected = 'all';

        match (true) {
            $statusParam === null => [
                $single = WorkOrderStatus::WaitingAcceptance,
                $selected = WorkOrderStatus::WaitingAcceptance->value,
            ],
            $statusParam === 'all' => null,
            $statusParam === 'processing' => [
                $multi = [WorkOrderStatus::Arrived, WorkOrderStatus::Installation],
                $selected = 'processing',
            ],
            default => [
                $single = WorkOrderStatus::tryFrom((string) $statusParam) ?? WorkOrderStatus::WaitingAcceptance,
                $selected = $single->value,
            ],
        };

        $workOrders = WorkOrder::query()
            ->with(['customer', 'assignments.technician.user', 'trackingSessions.tokens'])
            ->when($single !== null, fn ($query) => $query->where('status', $single))
            ->when($multi !== [], fn ($query) => $query->whereIn('status', $multi))
            ->latest('scheduled_start_at')
            ->paginate(20);

        $trackingLinks = [];

        foreach ($workOrders as $workOrder) {
            if ($workOrder->status !== WorkOrderStatus::OnTheWay) {
                continue;
            }

            $session = $workOrder->trackingSessions
                ->first(fn ($session): bool => $session->status === TrackingSessionStatus::Active);

            if ($session === null) {
                continue;
            }

            $token = $session->tokens
                ->first(fn ($token): bool => $token->status === TrackingTokenStatus::Active);

            if ($token?->token_plain_encrypted) {
                try {
                    $trackingLinks[$workOrder->getKey()] = rtrim(
                        (string) config('notifications.tracking.public_url'),
                        '/',
                    ).'/'.Crypt::decryptString($token->token_plain_encrypted);
                } catch (Throwable $exception) {
                    Log::warning('Gagal mendekripsi token tracking untuk dashboard.', [
                        'work_order_id' => $workOrder->getKey(),
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return view('dashboard.work-orders', [
            'workOrders' => $workOrders,
            'selectedStatus' => $selected,
            'statuses' => WorkOrderStatus::cases(),
            'trackingLinks' => $trackingLinks,
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

    public function overviewJson(): JsonResponse
    {
        $statusCounts = WorkOrder::query()
            ->selectRaw('status, COUNT(*) AS total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'status_counts' => $statusCounts->map(fn ($value): int => (int) $value),
            'waiting_acceptance' => (int) ($statusCounts[WorkOrderStatus::WaitingAcceptance->value] ?? 0),
            'on_the_way' => (int) ($statusCounts[WorkOrderStatus::OnTheWay->value] ?? 0),
            'installation' => (int) ($statusCounts[WorkOrderStatus::Installation->value] ?? 0)
                + (int) ($statusCounts[WorkOrderStatus::Arrived->value] ?? 0),
            'finished' => (int) ($statusCounts[WorkOrderStatus::Finished->value] ?? 0),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function storeWorkOrder(StoreLegacyWorkOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $scheduledAt = $validated['scheduled_start_at'] ?? null;
        if (filled($scheduledAt) && filled($validated['scheduled_start_time'] ?? null)) {
            $scheduledAt .= ' '.$validated['scheduled_start_time'];
        }

        try {
            $workOrder = $this->workOrders->createFromSales(
                $validated['legacy_sales_serial'],
                $validated['technician_legacy_serials'],
                $scheduledAt,
                $validated['notes'] ?? null,
                $request->user(),
                $validated['location_address'] ?? null,
                isset($validated['latitude']) ? (float) $validated['latitude'] : null,
                isset($validated['longitude']) ? (float) $validated['longitude'] : null,
                $validated['customer_phone'] ?? null,
                $validated['customer_email'] ?? null,
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

        $activeSession = $workOrder->trackingSessions
            ->first(fn ($session): bool => $session->status === TrackingSessionStatus::Active);

        $currentLocation = $activeSession !== null
            ? Cache::get("tracking:session:{$activeSession->getKey()}:current_location")
            : null;

        return view('dashboard.work-order', [
            'workOrder' => $workOrder,
            'currentLocation' => $currentLocation,
        ]);
    }

    public function updateWorkOrder(UpdateWorkOrderRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $validated = $request->validated();

        $scheduledAt = $validated['scheduled_start_at'];
        if (filled($validated['scheduled_start_time'] ?? null)) {
            $scheduledAt .= ' '.$validated['scheduled_start_time'];
        }

        $workOrder->update([
            'scheduled_start_at' => $scheduledAt,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($workOrder->serviceLocation !== null) {
            $workOrder->serviceLocation->update([
                'address' => $validated['location_address'] ?? $workOrder->serviceLocation->address,
                'latitude' => isset($validated['latitude'])
                    ? (float) $validated['latitude']
                    : $workOrder->serviceLocation->latitude,
                'longitude' => isset($validated['longitude'])
                    ? (float) $validated['longitude']
                    : $workOrder->serviceLocation->longitude,
            ]);
        }

        if ($workOrder->customer !== null) {
            $customerUpdate = [];

            if (filled($validated['customer_phone'] ?? null)) {
                $customerUpdate['phone'] = $validated['customer_phone'];
            }

            if (filled($validated['customer_email'] ?? null)) {
                $customerUpdate['email'] = $validated['customer_email'];
            }

            if ($customerUpdate !== []) {
                $workOrder->customer->update($customerUpdate);
            }
        }

        return back()->with('success', "Work Order {$workOrder->number} berhasil diperbarui.");
    }

    public function resetPinForm(): View
    {
        $this->ensureResetPinAccess();

        $users = User::query()->orderBy('email')->get(['id', 'name', 'email']);

        return view('dashboard.reset-pin', ['users' => $users]);
    }

    public function resetPin(ResetPinRequest $request): RedirectResponse
    {
        $this->ensureResetPinAccess();

        $user = User::query()->where('email', $request->validated('email'))->first();

        if ($user === null) {
            return back()->with('error', 'Email tidak ditemukan.')->withInput();
        }

        $user->update(['pin_hash' => Hash::make($request->validated('pin'))]);

        return back()->with('success', "PIN akun {$user->email} berhasil direset.");
    }

    private function ensureResetPinAccess(): void
    {
        abort_unless(
            in_array(auth()->user()->role, [UserRole::Administrator, UserRole::Coordinator], true),
            403,
        );
    }
}
