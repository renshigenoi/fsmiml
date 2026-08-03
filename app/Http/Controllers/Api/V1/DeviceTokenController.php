<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDeviceTokenRequest;
use App\Modules\Identity\Models\UserDeviceToken;
use Illuminate\Http\JsonResponse;

class DeviceTokenController extends Controller
{
    public function store(StoreDeviceTokenRequest $request): JsonResponse
    {
        $validated = $request->validated();

        /** @var UserDeviceToken $deviceToken */
        $deviceToken = $request->user()->deviceTokens()->updateOrCreate(
            ['token' => $validated['token']],
            [
                'platform' => $validated['platform'],
                'device_name' => $validated['device_name'] ?? null,
                'last_used_at' => now(),
            ],
        );

        return response()->json([
            'message' => 'Device token registered.',
            'device_token' => [
                'id' => $deviceToken->getKey(),
                'platform' => $deviceToken->platform,
            ],
        ], 201);
    }
}
