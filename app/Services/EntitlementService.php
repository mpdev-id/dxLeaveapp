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
    public function getEntitlements()
    {
        return EmployeeEntitlement::with(['user', 'leaveType'])->get();
    }

    public function findEntitlementById($id)
    {
        return EmployeeEntitlement::find($id);
    }

    public function createEntitlement(array $data)
    {
        $validator = Validator::make($data, [
            'user_id' => 'required|exists:users,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'year' => 'required|integer|min:2000',
            'initial_balance' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return EmployeeEntitlement::create($data);
    }

    public function updateEntitlement(EmployeeEntitlement $entitlement, array $data)
    {
        $validator = Validator::make($data, [
            'user_id' => 'exists:users,id',
            'leave_type_id' => 'exists:leave_types,id',
            'year' => 'integer|min:2000',
            'initial_balance' => 'numeric|min:0',
            'days_taken' => 'numeric|min:0',
            'carry_over_days' => 'numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $entitlement->update($data);
        return $entitlement;
    }

    public function deleteEntitlement(EmployeeEntitlement $entitlement)
    {
        $entitlement->delete();
    }

    public function hasSufficientBalance(User $user, int $leaveTypeId, int $daysNeeded): bool
    {
        $year = Carbon::now()->year;

        $entitlement = EmployeeEntitlement::where('user_id', $user->id)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if (!$entitlement) {
            return false; // No entitlement found for this leave type and year
        }

        $remainingBalance = $entitlement->initial_balance + $entitlement->carry_over_days - $entitlement->days_taken;

        return $remainingBalance >= $daysNeeded;
    }

    /**
     * Mengurangi saldo jatah cuti setelah permintaan disetujui penuh (FINAL APPROVAL).
     *
     * @param LeaveRequest $leaveRequest Permintaan cuti yang telah disetujui.
     * @return void
     */
    public function deductLeaveBalance(LeaveRequest $leaveRequest): void
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
}
