<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\Master\UserController;
use App\Http\Controllers\Admin\Master\DepartmentController;
use App\Http\Controllers\Admin\Master\LeaveTypeController;
use App\Http\Controllers\Admin\Master\PublicHolidayController;
use App\Http\Controllers\Admin\Master\EmployeeEntitlementController;
use App\Http\Controllers\Admin\Master\UserController as AdminMasterUserController; // Alias the Admin Master User Controller
use App\Http\Controllers\Admin\LeaveLogController; // Add this line
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
    Route::get('/users', [AdminMasterUserController::class, 'index'])->name('users.index');
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/leave-types', [LeaveTypeController::class, 'index'])->name('leave-types.index');
    Route::get('/public-holidays', [PublicHolidayController::class, 'index'])->name('public-holidays.index');
    Route::get('/employee-entitlements', [EmployeeEntitlementController::class, 'index'])->name('employee-entitlements.index');
    Route::get('/teams', [\App\Http\Controllers\Admin\Master\TeamController::class, 'index'])->name('teams.index');
    Route::get('/plants', [\App\Http\Controllers\Admin\Master\PlantController::class, 'index'])->name('plants.index');
    Route::get('/leave-request', function () {
        return view('admin.master.leave-request.index');
    })->name('leave-request');

    // New route for Leave Log (Employee List)
    Route::get('/leave-log', [AdminMasterUserController::class, 'leaveLogIndex'])->name('leave-log');
    // New route for Employee's individual Leave Log
    Route::get('/leave-log/{user}', [LeaveLogController::class, 'show'])->name('leave-log.show');

    // Push Notification Test
    Route::get('/push-notifications/test', function () {
        return view('admin.push-notifications.test');
    })->name('push-notifications.test');

    Route::resource('workflows', \App\Http\Controllers\Admin\Master\WorkflowController::class);
});

Route::get('/dashboard-member', [\App\Http\Controllers\Member\DashboardController::class, 'index'])->name('dashboard-member');
Route::get('/member/leaves', [\App\Http\Controllers\Member\LeaveRequestController::class, 'index'])->name('member.leaves.index');
Route::get('/member/leaves/create', [\App\Http\Controllers\Member\LeaveRequestController::class, 'create'])->name('member.leaves.create');
Route::get('/member/leaves/{leaveRequest}/edit', [\App\Http\Controllers\Member\LeaveRequestController::class, 'edit'])->name('member.leaves.edit');
Route::get('/member/leaves/{leaveRequest}/print', [\App\Http\Controllers\Member\LeaveRequestController::class, 'print'])->name('member.leaves.print');
Route::get('/member/profile', [\App\Http\Controllers\Member\ProfileController::class, 'index'])->name('member.profile.index');
Route::get('/member/approver-log', [\App\Http\Controllers\Member\ApproverLogController::class, 'index'])
    ->name('member.approver-log.index');
