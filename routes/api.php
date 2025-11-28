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

Route::get('master-data', [\App\Http\Controllers\API\MasterDataController::class, 'getAllMasterData']);


// --- Rute Autentikasi Publik (dengan Rate Limiting) ---
Route::middleware('throttle:10,1')->group(function () {
    Route::post('register', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'login']);
    Route::post('forgot-password', [UserController::class, 'forgotPassword']);
    Route::post('reset-password', [UserController::class, 'resetPassword']);
});

// --- Rute yang Membutuhkan Autentikasi (auth:sanctum) ---
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Rute User Standar
    Route::get('user', [UserController::class, 'fetch']);
    Route::get('user/leave-balances', [UserController::class, 'getLeaveBalances']);
    Route::patch('user/update-phone', [UserController::class, 'updatePhoneNumber']);
    Route::post('user/update-signature', [UserController::class, 'updateSignature']);
    Route::patch('user/change-password', [UserController::class, 'changePassword']);
    Route::post('user/test-whatsapp', [UserController::class, 'testWhatsApp']);
    Route::post('user/test-push', [\App\Http\Controllers\API\TestPushController::class, 'sendTest']);
    Route::post('logout', [UserController::class, 'logout']);

    // --- Rute Modul Cuti (Leave Requests) ---
    
    // 1. Pengajuan dan Daftar Cuti (Akses oleh Karyawan & Manajer)
    Route::get('leave-requests/suggestions', [LeaveRequestController::class, 'getSuggestions']);
    Route::resource('leave-requests', LeaveRequestController::class)->only(['index', 'store', 'update', 'show', 'destroy']);

    // 2. Tindakan Persetujuan/Penolakan Cuti
    // Endpoint ini dilindungi oleh Spatie Middleware: hanya user dengan peran 'manager' ATAU izin 'approve leave request' yang bisa mengakses
    Route::patch('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'handleApproval'])
        ->middleware('role_or_permission:manager|approve leave request'); 
    
    // 3. Approver Log - History of approvals by the current user
    Route::get('approver-log', [LeaveRequestController::class, 'getApproverLog'])
        ->middleware('role_or_permission:manager|approve leave request'); 

    // Push Notifications
    Route::post('push/subscribe', [\App\Http\Controllers\API\PushSubscriptionController::class, 'store']);
    Route::post('push/unsubscribe', [\App\Http\Controllers\API\PushSubscriptionController::class, 'destroy']);
});

Route::get('departments', [DepartmentController::class,'index'])->name('globalDepartments');
// --- Rute Administrasi Data Master (Hanya untuk Admin) ---
Route::middleware(['auth:sanctum', 'role:Super Admin', 'throttle:120,1'])->prefix('admin/master')->group(function () {
    Route::apiResource('users', AdminUserController::class);
    Route::get('users/{user}/leave-requests', [AdminLeaveRequestController::class, 'getEmployeeLeaveRequests']);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('leave-types', LeaveTypeController::class);
    Route::apiResource('public-holidays', PublicHolidayController::class);
    Route::apiResource('employee-entitlements', EmployeeEntitlementController::class);
    Route::apiResource('leave-requests', AdminLeaveRequestController::class)->names('admin.leave-requests');
    Route::patch('leave-requests/{leaveRequest}/handle-approval', [AdminLeaveRequestController::class, 'handleApproval'])->name('admin.leave-requests.handle-approval');
    Route::post('leave-requests/{leaveRequest}/submit', [AdminLeaveRequestController::class, 'submit'])->name('admin.leave-requests.submit');

    Route::get('users/{user}/status', [AdminUserController::class, 'getStatus'])->name('admin.users.status');
    Route::get('roles', [\App\Http\Controllers\API\MasterDataController::class, 'roles'])->name('admin.roles.index');
    
    // Push Notification Testing
    Route::get('push-notifications/subscribed-users', [\App\Http\Controllers\API\Admin\PushNotificationTestController::class, 'getSubscribedUsers']);
    Route::post('push-notifications/test', [\App\Http\Controllers\API\Admin\PushNotificationTestController::class, 'sendTest']);
    Route::post('push-notifications/test-all', [\App\Http\Controllers\API\Admin\PushNotificationTestController::class, 'sendToAll']);
});
// --- Rute Administrasi Dasbor (Hanya untuk Admin) ---
Route::middleware(['auth:sanctum', 'role:Super Admin', 'throttle:120,1'])->prefix('admin/dashboard')->group(function () {
    Route::get('stats', [\App\Http\Controllers\API\Admin\DashboardController::class, 'getStats']);
    Route::get('recent-activity', [\App\Http\Controllers\API\Admin\DashboardController::class, 'getRecentActivity']);
    Route::get('upcoming-leaves', [\App\Http\Controllers\API\Admin\DashboardController::class, 'getUpcomingLeaves']);
    Route::get('leave-calendar', [\App\Http\Controllers\API\Admin\DashboardController::class, 'getLeaveCalendar']);
    Route::get('leave-requests-by-date', [\App\Http\Controllers\API\Admin\DashboardController::class, 'getLeaveRequestsByDate']);
    Route::get('leave-chart-data', [\App\Http\Controllers\API\Admin\DashboardController::class, 'getLeaveChartData']);
});

