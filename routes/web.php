<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\Master\UserController;
use App\Http\Controllers\Admin\Master\DepartmentController;
use App\Http\Controllers\Admin\Master\LeaveTypeController;
use App\Http\Controllers\Admin\Master\PublicHolidayController;
use App\Http\Controllers\Admin\Master\EmployeeEntitlementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'index']);

Route::get('/login', [LoginController::class, 'index'])->name('login');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/leave-types', [LeaveTypeController::class, 'index'])->name('leave-types.index');
    Route::get('/public-holidays', [PublicHolidayController::class, 'index'])->name('public-holidays.index');
    Route::get('/employee-entitlements', [EmployeeEntitlementController::class, 'index'])->name('employee-entitlements.index');
});

