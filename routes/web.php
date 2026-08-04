<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\MobileController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\TrackingPageController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('mobile', [MobileController::class, 'app'])->name('mobile.app');
Route::get('mobile/manifest.webmanifest', [MobileController::class, 'manifest']);
Route::get('mobile/sw.js', [MobileController::class, 'serviceWorker']);
Route::get('tracking/{token}', [TrackingPageController::class, 'show'])->name('tracking.show');

Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/input', [DashboardController::class, 'input'])->name('dashboard.input');
    Route::get('dashboard/work-orders', [DashboardController::class, 'workOrders'])->name('dashboard.work-orders');
    Route::get('dashboard/technicians', [DashboardController::class, 'technicians'])->name('dashboard.technicians');
    Route::get('dashboard/api/sales', [DashboardController::class, 'searchSales']);
    Route::get('dashboard/api/technicians', [DashboardController::class, 'techniciansJson']);
    Route::get('dashboard/api/overview', [DashboardController::class, 'overviewJson']);
    Route::post('dashboard/work-orders', [DashboardController::class, 'storeWorkOrder']);
    Route::get('dashboard/work-orders/{workOrder}', [DashboardController::class, 'showWorkOrder'])->name('dashboard.work-orders.show');

    Route::get('dashboard/profile', [ProfileController::class, 'show'])->name('dashboard.profile');
    Route::post('dashboard/profile', [ProfileController::class, 'update']);
});
