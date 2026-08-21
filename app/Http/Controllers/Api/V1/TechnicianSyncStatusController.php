<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TechnicianSyncStatusController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'pending_count' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $technician = $request->user()->technician;
        if ($technician) {
            $technician->update([
                'offline_sync_pending_count' => $data['pending_count'],
                'offline_sync_last_reported_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Status sinkronisasi diperbarui.']);
    }
}
