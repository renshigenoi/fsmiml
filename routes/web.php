<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AttendanceAdminController;
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
    Route::get('dashboard/attendance', [AttendanceAdminController::class, 'index'])->name('dashboard.attendance');
    Route::post('dashboard/attendance/locations', [AttendanceAdminController::class, 'storeLocation'])->name('dashboard.attendance.locations.store');
    Route::post('dashboard/attendance/locations/{location}', [AttendanceAdminController::class, 'updateLocation'])->name('dashboard.attendance.locations.update');
    Route::post('dashboard/attendance/technicians/{technician}', [AttendanceAdminController::class, 'updateTechnician'])->name('dashboard.attendance.technicians.update');
    Route::post('dashboard/users/{user}/toggle-fake-gps', [DashboardController::class, 'toggleFakeGps'])->name('dashboard.users.toggle-fake-gps');
    Route::post('dashboard/attendance/leaves/{leaveRequest}', [AttendanceAdminController::class, 'reviewLeave'])->name('dashboard.attendance.leaves.review');
    Route::get('dashboard/reset-pin', [DashboardController::class, 'resetPinForm'])->name('dashboard.reset-pin');
    Route::post('dashboard/reset-pin', [DashboardController::class, 'resetPin']);
    Route::get('dashboard/api/sales', [DashboardController::class, 'searchSales']);
    Route::get('dashboard/api/sales/{serial}/details', [DashboardController::class, 'salesDetailsJson']);
    Route::get('dashboard/api/technicians', [DashboardController::class, 'techniciansJson']);
    Route::get('dashboard/api/overview', [DashboardController::class, 'overviewJson']);
    Route::post('dashboard/work-orders', [DashboardController::class, 'storeWorkOrder']);
    Route::get('dashboard/work-orders/{workOrder}', [DashboardController::class, 'showWorkOrder'])->name('dashboard.work-orders.show');
    Route::post('dashboard/work-orders/{workOrder}/update', [DashboardController::class, 'updateWorkOrder'])->name('dashboard.work-orders.update');

    Route::get('dashboard/profile', [ProfileController::class, 'show'])->name('dashboard.profile');
    Route::post('dashboard/profile', [ProfileController::class, 'update']);
});
