<?php

use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\LeaveRequestController; // Import Controller Cuti
use App\Http\Controllers\API\EmployeeEntitlementController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\LeaveTypeController;
use App\Http\Controllers\API\PublicHolidayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- Rute Autentikasi Publik ---
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('forgot-password', [UserController::class, 'forgotPassword']);
Route::post('reset-password', [UserController::class, 'resetPassword']);

// --- Rute yang Membutuhkan Autentikasi (auth:sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    // Rute User Standar
    Route::get('user', [UserController::class, 'fetch']);
    Route::post('logout', [UserController::class, 'logout']);

    // Rute untuk mengelola jatah cuti karyawan (Employee Entitlements)
    Route::apiResource('employee-entitlements', EmployeeEntitlementController::class);

    // --- Rute Modul Cuti (Leave Requests) ---
    
    // 1. Pengajuan dan Daftar Cuti (Akses oleh Karyawan & Manajer)
    Route::resource('leave-requests', LeaveRequestController::class)->only(['index', 'store']);

    // 2. Tindakan Persetujuan/Penolakan Cuti
    // Endpoint ini dilindungi oleh Spatie Middleware: hanya user dengan peran 'manager' ATAU izin 'approve leave request' yang bisa mengakses
    Route::patch('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'handleApproval'])
        ->middleware('role_or_permission:manager|approve leave request'); 
});

// --- Rute Administrasi Data Master (Hanya untuk Admin) ---
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/master')->group(function () {
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('leave-types', LeaveTypeController::class);
    Route::apiResource('public-holidays', PublicHolidayController::class);
});

