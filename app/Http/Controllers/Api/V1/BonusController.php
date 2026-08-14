<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Legacy\Services\LegacyDataSourceService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BonusController extends Controller
{
    public function index(Request $request, LegacyDataSourceService $legacy): JsonResponse
    {
        $date = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ])['date'];

        $serial = $request->user()->technician?->external_serial;

        if (blank($serial)) {
            throw new AuthorizationException('Akun teknisi belum terhubung ke data legacy.');
        }

        $rows = array_map(static function (object $row): array {
            $total = is_numeric($row->total ?? null) ? (float) $row->total : 0.0;

            return [
                'technician_name' => $row->teknisi_name ?? null,
                'payment_date' => $row->date ?? null,
                'invoice_number' => collect([
                    $row->sales_invoice_no_car ?? null,
                    $row->sales_invoice_no_building ?? null,
                    $row->sales_invoice_no_materials ?? null,
                ])->first(fn (?string $invoice): bool => filled($invoice)),
                'total' => $total,
            ];
        }, $legacy->bonusesForTechnician($serial, $date));

        return response()->json([
            'data' => $rows,
            'meta' => [
                'date' => $date,
                'total_bonus' => array_sum(array_column($rows, 'total')),
            ],
        ]);
    }
}
