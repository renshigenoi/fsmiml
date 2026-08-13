<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPinRequest;
use App\Http\Requests\Api\V1\StoreLegacyWorkOrderRequest;
use App\Http\Requests\UpdateWorkOrderRequest;
use App\Models\User;
use App\Modules\Assignment\Enums\AssignmentStatus;
use App\Modules\Assignment\Exceptions\InvalidAssignment;
use App\Modules\Assignment\Events\AssignmentCreated;
use App\Modules\Assignment\Models\Assignment;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\Legacy\Services\LegacyDataSourceService;
use App\Modules\Legacy\Services\LegacyTechnicianImporter;
use App\Modules\Legacy\Services\LegacyWorkOrderService;
use App\Modules\Legacy\Support\LegacyRowFormatter;
use App\Modules\Legacy\Support\SalesDetailMapper;
use App\Modules\Tracking\Models\TrackingSession;
use App\Modules\Tracking\Enums\TrackingSessionStatus;
use App\Modules\Tracking\Services\TrackingTokenService;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly LegacyDataSourceService $legacy,
        private readonly LegacyWorkOrderService $workOrders,
        private readonly LegacyTechnicianImporter $technicianImporter,
        private readonly TrackingTokenService $trackingTokens,
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
        $rangeParam = $request->query('range');
        $perPageParam = $request->query('per_page');
        $searchParam = trim((string) $request->query('q'));

        $perPage = match (strtolower((string) $perPageParam)) {
            '25' => 25,
            '50' => 50,
            'all' => 100000,
            default => 10,
        };

        $selectedRange = match ((string) $rangeParam) {
            'all', '0' => null,
            '7' => 7,
            '14' => 14,
            default => 3, // default: 3 hari terakhir
        };

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
            ->when($selectedRange !== null, fn ($query) => $query->where('created_at', '>=', now()->subDays($selectedRange)))
            ->when($searchParam !== '', fn ($query) => $query->where(function ($query) use ($searchParam) {
                $pattern = '%'.$searchParam.'%';

                $query->where('number', 'ilike', $pattern)
                    ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'ilike', $pattern));
            }))
            ->latest('scheduled_start_at')
            ->paginate($perPage)
            ->withQueryString();

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

            try {
                $issued = $this->trackingTokens->ensurePlaintextLink($session);
                if ($issued !== null) {
                    $trackingLinks[$workOrder->getKey()] = rtrim(
                        (string) config('notifications.tracking.public_url'),
                        '/',
                    ).'/'.$issued->plainToken;
                }
            } catch (Throwable $exception) {
                Log::warning('Gagal menyiapkan link tracking untuk dashboard.', [
                    'work_order_id' => $workOrder->getKey(),
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $data = [
            'workOrders' => $workOrders,
            'selectedStatus' => $selected,
            'statuses' => WorkOrderStatus::cases(),
            'trackingLinks' => $trackingLinks,
            'selectedRange' => $selectedRange,
            'selectedPerPage' => $perPageParam !== null && strtolower((string) $perPageParam) === 'all'
                ? 'all'
                : $perPage,
            'search' => $searchParam,
        ];

        if ($request->query('partial') === '1') {
            return view('dashboard.work-orders-table', $data);
        }

        return view('dashboard.work-orders', $data);
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

    public function salesDetailsJson(string $serial): JsonResponse
    {
        $rows = array_map(
            fn (object $row): array => LegacyRowFormatter::salesDetail($row),
            $this->legacy->salesDetails($serial),
        );

        return response()
            ->json(['data' => $rows])
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
            'salesOrder',
            'assignments.technician.user',
            'statusHistories',
            'trackingSessions',
            'photos',
        ]);

        $activeSession = $workOrder->trackingSessions
            ->first(fn ($session): bool => $session->status === TrackingSessionStatus::Active);

        $currentLocation = $activeSession !== null
            ? Cache::get("tracking:session:{$activeSession->getKey()}:current_location")
            : null;

        $trackingLink = null;

        if ($activeSession !== null) {
            try {
                $issued = $this->trackingTokens->ensurePlaintextLink($activeSession);
                $trackingLink = $issued !== null
                    ? rtrim((string) config('notifications.tracking.public_url'), '/').'/'.$issued->plainToken
                    : null;
            } catch (Throwable $exception) {
                Log::warning('Gagal menyiapkan link tracking untuk detail work order.', [
                    'work_order_id' => $workOrder->getKey(),
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $salesType = $workOrder->salesOrder?->source_payload['sales_type'] ?? null;
        $salesPayload = $workOrder->salesOrder?->source_payload ?? [];
        $salesDetails = [];

        if ($salesSerial = $workOrder->salesOrder?->external_id) {
            $salesDetails = array_map(
                fn (object $row): array => LegacyRowFormatter::salesDetail($row),
                $this->legacy->salesDetails($salesSerial),
            );
        }

        $carInfo = [
            'brand' => $salesPayload['car_brand'] ?? null,
            'model' => $salesPayload['car_model'] ?? null,
            'chassis_no' => $salesPayload['chassis_no'] ?? null,
            'police_no' => $salesPayload['police_no'] ?? null,
            'installation_type' => SalesDetailMapper::installationTypeLabel(
                $salesPayload['installation_type'] ?? null,
            ),
        ];

        return view('dashboard.work-order', [
            'workOrder' => $workOrder,
            'currentLocation' => $currentLocation,
            'trackingLink' => $trackingLink,
            'salesDetails' => $salesDetails,
            'salesType' => $salesType,
            'carInfo' => $carInfo,
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

        $this->syncTechnicians($workOrder, $validated['technician_legacy_serials'] ?? [], $request->user());

        return back()->with('success', "Work Order {$workOrder->number} berhasil diperbarui.");
    }

    /**
     * Sinkronkan daftar teknisi: tambah yang baru, hapus yang pending.
     * Teknisi yang sudah menerima tugas tidak bisa dihapus.
     *
     * @param  array<int, string>  $serials
     */
    private function syncTechnicians(WorkOrder $workOrder, array $serials, User $actor): void
    {
        $desired = collect($serials)->filter()->unique();
        $workOrder->loadMissing(['assignments.technician.user']);

        $currentSerials = $workOrder->assignments
            ->pluck('technician.external_serial')
            ->filter();

        $newSerials = $desired->diff($currentSerials)->values()->all();

        if ($newSerials !== []) {
            $technicians = $this->technicianImporter->importBySerials($newSerials);

            foreach ($technicians as $technician) {
                $assignment = Assignment::query()->create([
                    'work_order_id' => $workOrder->getKey(),
                    'technician_id' => $technician->getKey(),
                    'status' => AssignmentStatus::Pending,
                    'assigned_by' => $actor->getKey(),
                    'assigned_at' => now(),
                ]);

                TrackingSession::query()->create([
                    'work_order_id' => $workOrder->getKey(),
                    'assignment_id' => $assignment->getKey(),
                    'status' => TrackingSessionStatus::Pending,
                    'realtime_channel' => Str::random(32),
                ]);

                AssignmentCreated::dispatch($assignment);
            }
        }

        foreach ($workOrder->assignments as $assignment) {
            if ($desired->contains($assignment->technician?->external_serial)) {
                continue;
            }

            if ($assignment->status === AssignmentStatus::Accepted) {
                throw ValidationException::withMessages([
                    'technician_legacy_serials' => sprintf(
                        'Teknisi %s sudah menerima tugas, tidak bisa dihapus.',
                        $assignment->technician?->user?->name ?? 'tersebut',
                    ),
                ]);
            }

            if ($assignment->status === AssignmentStatus::Pending) {
                $assignment->update(['status' => AssignmentStatus::Cancelled]);

                TrackingSession::query()
                    ->where('assignment_id', $assignment->getKey())
                    ->where('status', TrackingSessionStatus::Pending->value)
                    ->update([
                        'status' => TrackingSessionStatus::Cancelled,
                        'closed_reason' => 'cancelled',
                        'ended_at' => now(),
                    ]);
            }
        }
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
