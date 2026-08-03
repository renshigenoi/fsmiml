<?php

namespace App\Modules\Legacy\Support;

final class LegacyRowFormatter
{
    /**
     * @return array<string, mixed>
     */
    public static function technician(object $row): array
    {
        return [
            'serial' => $row->serial ?? null,
            'user_id' => $row->user_id ?? null,
            'full_name' => $row->full_name ?? null,
            'cell_phone' => $row->cell_phone ?? null,
            'home_phone' => $row->home_phone ?? null,
        ];
    }

    /**
     * Safe subset of the legacy sales columns. Extend once the dashboard
     * display fields are confirmed.
     *
     * @return array<string, mixed>
     */
    public static function sales(object $row): array
    {
        return [
            'spk_no' => $row->spk_no ?? null,
            'serial' => $row->serial ?? null,
            'sales_type' => $row->sales_type ?? null,
            'status' => $row->status ?? null,
            'sales_invoice_no_car' => $row->sales_invoice_no_car ?? null,
            'sales_order_no_car' => $row->sales_order_no_car ?? null,
            'sales_invoice_no_building' => $row->sales_invoice_no_building ?? null,
            'sales_order_no_building' => $row->sales_order_no_building ?? null,
            'sales_invoice_no_materials' => $row->sales_invoice_no_materials ?? null,
            'sales_order_no_materials' => $row->sales_order_no_materials ?? null,
            'customer_name' => $row->customer_name ?? null,
            'address' => $row->address ?? null,
            'city' => $row->city ?? null,
            'state' => $row->state ?? null,
            'zip' => $row->zip ?? null,
            'cell_phone' => $row->cell_phone ?? null,
            'contact_person' => $row->contact_person ?? null,
            'car_brand' => $row->car_brand ?? null,
            'car_model' => $row->car_model ?? null,
            'installation_date' => $row->sellingdate ?? $row->installation_date ?? null,
            'pairing_date' => $row->techniciandate ?? $row->pairing_date ?? null,
        ];
    }
}
