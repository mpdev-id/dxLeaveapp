<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\Master\UserController;
use App\Http\Controllers\Admin\Master\DepartmentController;
use App\Http\Controllers\Admin\Master\LeaveTypeController;
use App\Http\Controllers\Admin\Master\PublicHolidayController;
use App\Http\Controllers\Admin\Master\EmployeeEntitlementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'index']);

// Auth Routes
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('register', function () {
    return view('auth.register');
})->name('register');

Route::post('register', function (Request $request) {
    // Placeholder for registration logic
    return redirect()->route('login')->with('status', 'Registration functionality not yet implemented.');
});

Route::get('forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::post('forgot-password', function (Request $request) {
    // Placeholder for password reset logic
    return back()->with('status', 'Password reset functionality not yet implemented.');
})->name('password.email');


Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.master.dashboard.index');
    })->name('dashboard.index');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/leave-types', [LeaveTypeController::class, 'index'])->name('leave-types.index');
    Route::get('/public-holidays', [PublicHolidayController::class, 'index'])->name('public-holidays.index');
    Route::get('/employee-entitlements', [EmployeeEntitlementController::class, 'index'])->name('employee-entitlements.index');
});
