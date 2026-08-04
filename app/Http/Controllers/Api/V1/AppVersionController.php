<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AppVersionController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'version' => (string) config('mobile.version', '1.0.0'),
            'download_url' => config('mobile.download_url'),
            'update_required' => (bool) config('mobile.update_required', false),
            'checked_at' => now()->toIso8601String(),
        ]);
    }
}
