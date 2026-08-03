<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/api/sales', [DashboardController::class, 'searchSales']);
    Route::get('dashboard/api/technicians', [DashboardController::class, 'technicians']);
    Route::post('dashboard/work-orders', [DashboardController::class, 'storeWorkOrder']);
    Route::get('dashboard/work-orders/{workOrder}', [DashboardController::class, 'showWorkOrder'])->name('dashboard.work-orders.show');

    Route::get('dashboard/profile', [ProfileController::class, 'show'])->name('dashboard.profile');
    Route::post('dashboard/profile', [ProfileController::class, 'update']);
});
