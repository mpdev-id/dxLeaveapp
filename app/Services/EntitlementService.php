<?php

namespace App\Services;

use App\Models\EmployeeEntitlement;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EntitlementService
{
    // ... (existing methods: getEntitlements, findEntitlementById, createEntitlement, etc.)

    /**
     * Mengurangi saldo jatah cuti setelah permintaan disetujui penuh (FINAL APPROVAL).
     *
     * @param LeaveRequest $leaveRequest Permintaan cuti yang telah disetujui.
     * @return void
     */
    public function deductEntitlement(LeaveRequest $leaveRequest): void
    {
        $year = Carbon::parse($leaveRequest->start_date)->year;

        $entitlement = EmployeeEntitlement::where('user_id', $leaveRequest->user_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('year', $year)
            ->first();

        if ($entitlement) {
            // Gunakan increment untuk operasi atomik & menghindari race condition
            $entitlement->increment('days_taken', $leaveRequest->duration_days);
        } else {
            // Log jika jatah cuti untuk tahun tersebut tidak ditemukan, ini seharusnya tidak terjadi dalam alur normal
            Log::warning("Entitlement record not found for user {$leaveRequest->user_id} for leave type {$leaveRequest->leave_type_id} in year {$year}. Could not deduct leave days.");
        }
    }

    // ... (existing methods: hasSufficientBalance, getCurrentBalance)

    // Note: To keep the snippet clean, I'm only showing the changed method.
    // The full file content will be updated with this logic.
}
