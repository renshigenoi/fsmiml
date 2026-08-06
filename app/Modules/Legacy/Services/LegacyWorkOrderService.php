<?php

namespace App\Modules\Legacy\Services;

use App\Models\User;
use App\Modules\Assignment\Services\AssignmentService;
use App\Modules\Customer\Models\Customer;
use App\Modules\Customer\Models\ServiceLocation;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\WorkOrder\Enums\WorkOrderStatus;
use App\Modules\WorkOrder\Models\WorkOrder;
use App\Modules\WorkOrder\Models\WorkOrderStatusHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Creates an FSM Work Order from a legacy sales record: copies the customer,
 * service location, sales order and items into PostgreSQL, imports the
 * selected technicians, and assigns the whole team in one transaction.
 */
class LegacyWorkOrderService
{
    public function __construct(
        private readonly LegacyDataSourceService $legacy,
        private readonly LegacyTechnicianImporter $technicianImporter,
        private readonly AssignmentService $assignments,
    ) {}

    /**
     * @param  array<int, string>  $technicianSerials
     */
    public function createFromSales(
        string $salesSerial,
        array $technicianSerials,
        ?string $scheduledStartAt,
        ?string $notes,
        User $actor,
        ?string $locationAddress = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $customerPhone = null,
        ?string $customerEmail = null,
    ): WorkOrder {
        $row = $this->legacy->salesBySerial($salesSerial);

        if ($row === null) {
            throw ValidationException::withMessages([
                'legacy_sales_serial' => 'Sales record not found in the legacy database.',
            ]);
        }

        return DB::transaction(function () use (
            $row,
            $technicianSerials,
            $scheduledStartAt,
            $notes,
            $actor,
            $locationAddress,
            $latitude,
            $longitude,
            $customerPhone,
            $customerEmail,
        ): WorkOrder {
            $customer = $this->upsertCustomer($row, $customerPhone, $customerEmail);
            $location = $this->upsertServiceLocation($customer, $row, $locationAddress, $latitude, $longitude);
            $salesOrder = $this->upsertSalesOrder($customer, $row);
            $workOrder = $this->createWorkOrder($salesOrder, $customer, $location, $row, $scheduledStartAt, $notes, $actor);

            $this->syncItems($salesOrder, $workOrder, $row);

            $technicians = $this->technicianImporter->importBySerials($technicianSerials);

            if ($technicians->isEmpty()) {
                throw ValidationException::withMessages([
                    'technician_legacy_serials' => 'None of the selected technicians were found in the legacy database.',
                ]);
            }

            $this->assignments->assignMany($workOrder, $technicians->pluck('id')->all(), $actor);

            return $workOrder;
        });
    }

    private function upsertCustomer(
        object $row,
        ?string $phoneOverride = null,
        ?string $emailOverride = null,
    ): Customer
    {
        $externalId = filled($row->user_serial) ? (string) $row->user_serial : 'sales-'.$row->serial;

        return Customer::query()->updateOrCreate(
            ['external_id' => $externalId],
            [
                'name' => $row->customer_name ?? 'Customer '.$row->serial,
                'phone' => filled($phoneOverride) ? $phoneOverride : ($row->cell_phone ?? null),
                'email' => filled($emailOverride) ? $emailOverride : ($row->customer_email ?? null),
            ],
        );
    }

    private function upsertServiceLocation(
        Customer $customer,
        object $row,
        ?string $addressOverride,
        ?float $latitude,
        ?float $longitude,
    ): ServiceLocation {
        $address = trim((string) ($addressOverride ?: ($row->installation_address ?: $row->address)));

        if (blank($address) && ($latitude === null || $longitude === null)) {
            throw ValidationException::withMessages([
                'legacy_sales_serial' => 'Sales record has no installation address. Set the location on the map first.',
            ]);
        }

        if (blank($address)) {
            $address = 'Lokasi '.rtrim(rtrim(number_format((float) $latitude, 6), '0'), '.').', '
                .rtrim(rtrim(number_format((float) $longitude, 6), '0'), '.');
        }

        return ServiceLocation::query()->updateOrCreate(
            ['customer_id' => $customer->getKey(), 'address' => $address],
            [
                'label' => filled($row->spk_no) ? 'SPK '.$row->spk_no : null,
                'city' => $row->city ?? null,
                'province' => $row->state ?? null,
                'postal_code' => $row->zip ?? null,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
        );
    }

    private function upsertSalesOrder(Customer $customer, object $row): SalesOrder
    {
        return SalesOrder::query()->updateOrCreate(
            ['external_id' => (string) $row->serial],
            [
                'invoice_number' => $row->sales_invoice_no_car ?: (filled($row->spk_no) ? $row->spk_no : 'SO-'.$row->serial),
                'customer_id' => $customer->getKey(),
                'status' => (string) ($row->status ?? ''),
                'ordered_at' => $row->installation_date ? Carbon::parse($row->installation_date) : null,
                'source_payload' => json_decode(json_encode($row), true),
                'synced_at' => now(),
            ],
        );
    }

    private function createWorkOrder(
        SalesOrder $salesOrder,
        Customer $customer,
        ServiceLocation $location,
        object $row,
        ?string $scheduledStartAt,
        ?string $notes,
        User $actor,
    ): WorkOrder {
        $number = (string) ($row->spk_no ?? '');

        if ($number === '') {
            throw ValidationException::withMessages([
                'legacy_sales_serial' => 'Sales record has no SPK number.',
            ]);
        }

        if (WorkOrder::query()->where('number', $number)->exists()) {
            $existing = WorkOrder::query()->where('number', $number)->first();
            throw ValidationException::withMessages([
                'spk_no' => "SPK {$number} sudah pernah dibuat"
                    .($existing ? " (WO #{$existing->id}, status: {$existing->status->value})" : '')
                    .'. Gunakan nomor SPK yang berbeda.',
            ]);
        }

        $scheduledStart = filled($scheduledStartAt)
            ? Carbon::parse($scheduledStartAt)
            : ($row->installation_date ? Carbon::parse($row->installation_date) : now());

        $workOrder = WorkOrder::query()->create([
            'number' => $number,
            'sales_order_id' => $salesOrder->getKey(),
            'customer_id' => $customer->getKey(),
            'service_location_id' => $location->getKey(),
            'work_type' => 'installation',
            'status' => WorkOrderStatus::Draft,
            'scheduled_start_at' => $scheduledStart,
            'scheduled_end_at' => null,
            'notes' => $notes,
            'created_by' => $actor->getKey(),
        ]);

        WorkOrderStatusHistory::query()->create([
            'work_order_id' => $workOrder->getKey(),
            'from_status' => null,
            'to_status' => WorkOrderStatus::Draft,
            'actor_user_id' => $actor->getKey(),
            'reason' => 'created_from_legacy_sales',
            'occurred_at' => now(),
        ]);

        return $workOrder;
    }

    private function syncItems(SalesOrder $salesOrder, WorkOrder $workOrder, object $row): void
    {
        $productName = trim(trim((string) ($row->car_brand ?? '')).' '.trim((string) ($row->car_model ?? '')));

        if ($productName === '') {
            return;
        }

        $salesItem = $salesOrder->items()->create([
            'product_code' => $row->car_type_serial ? (string) $row->car_type_serial : null,
            'product_name' => $productName,
            'window_film_desc' => filled($row->window_film_desc ?? null) ? (string) $row->window_film_desc : null,
            'quantity' => 1,
        ]);

        $workOrder->items()->create([
            'sales_order_item_id' => $salesItem->getKey(),
            'product_code' => $salesItem->product_code,
            'product_name' => $productName,
            'window_film_desc' => $salesItem->window_film_desc,
            'quantity' => 1,
        ]);
    }
}
