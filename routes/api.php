<?php

use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\LeaveRequestController; // Import Controller Cuti
use App\Http\Controllers\API\EmployeeEntitlementController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\LeaveTypeController;
use App\Http\Controllers\API\PublicHolidayController;
use App\Http\Controllers\API\Admin\UserController as AdminUserController;
use App\Http\Controllers\API\Admin\LeaveRequestController as AdminLeaveRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- Rute Autentikasi Publik ---
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('forgot-password', [UserController::class, 'forgotPassword']);
Route::post('reset-password', [UserController::class, 'resetPassword']);

// --- Rute yang Membutuhkan Autentikasi (auth:sanctum) ---
Route::middleware(['auth:sanctum', 'role:Super Admin'])->prefix('admin/master')->group(function () {
    Route::apiResource('users', AdminUserController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('leave-types', LeaveTypeController::class);
    Route::apiResource('public-holidays', PublicHolidayController::class);
    Route::apiResource('employee-entitlements', EmployeeEntitlementController::class);
    Route::apiResource('leave-requests', AdminLeaveRequestController::class)->names('admin.leave-requests');
});

