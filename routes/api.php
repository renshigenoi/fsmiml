<?php

use App\Http\Controllers\Api\V1\AssignmentController;
use App\Http\Controllers\Api\V1\AppVersionController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DeviceTokenController;
use App\Http\Controllers\Api\V1\LegacyController;
use App\Http\Controllers\Api\V1\TrackingLocationController;
use App\Http\Controllers\Api\V1\TrackingTokenController;
use App\Http\Controllers\Api\V1\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('app/version', [AppVersionController::class, 'show']);
    Route::get('public/tracking/{token}', [TrackingTokenController::class, 'show'])->middleware('throttle:public-tracking');

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/change-password', [AuthController::class, 'changePassword']);
        Route::post('auth/pin', [AuthController::class, 'setPin']);
        Route::post('auth/pin/verify', [AuthController::class, 'verifyPin']);
        Route::delete('auth/logout', [AuthController::class, 'logout']);

        Route::get('work-orders', [WorkOrderController::class, 'index']);
        Route::post('work-orders', [WorkOrderController::class, 'store']);
        Route::get('work-orders/{workOrder}', [WorkOrderController::class, 'show']);
        Route::post('work-orders/{workOrder}/assignments', [AssignmentController::class, 'store']);
        Route::post('work-orders/{workOrder}/start-trip', [WorkOrderController::class, 'startTrip']);
        Route::post('work-orders/{workOrder}/arrive', [WorkOrderController::class, 'arrive']);
        Route::post('work-orders/{workOrder}/start-installation', [WorkOrderController::class, 'startInstallation']);
        Route::post('work-orders/{workOrder}/finish', [WorkOrderController::class, 'finish']);
        Route::post('work-orders/{workOrder}/cancel', [WorkOrderController::class, 'cancel']);
        Route::post('work-orders/{workOrder}/fail', [WorkOrderController::class, 'fail']);

        Route::post('assignments/{assignment}/accept', [AssignmentController::class, 'accept']);
        Route::post('assignments/{assignment}/reject', [AssignmentController::class, 'reject']);
        Route::post('tracking-sessions/{trackingSession}/locations', [TrackingLocationController::class, 'store'])
            ->middleware('throttle:tracking');
        Route::post('tracking-sessions/{trackingSession}/tokens', [TrackingTokenController::class, 'store']);
        Route::post('device-tokens', [DeviceTokenController::class, 'store']);
        Route::get('legacy/technicians', [LegacyController::class, 'technicians']);
        Route::get('legacy/sales', [LegacyController::class, 'sales']);
        Route::post('legacy/work-orders', [LegacyController::class, 'storeWorkOrder']);
    });
});
